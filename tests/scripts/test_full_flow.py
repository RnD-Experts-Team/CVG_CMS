"""
Full e2e exercise of the admin Project endpoints, mirroring exactly what the
Next.js dashboard sends (FormData multipart, POST for both create and update,
Origin header to validate CORS).

Asserts:
- create with no content (backend nullable) succeeds
- create with images succeeds
- update existing project: change title, keep+reorder existing images, remove one, add one
- update with no images at all succeeds (this is what user did with project 2)
- preflight returns proper CORS headers
- delete cleans up
"""

import json
import mimetypes
import os
import urllib.request
import urllib.error
import uuid

BASE = "http://127.0.0.1:8000/api"
ORIGIN = "http://localhost:3000"
SAMPLES = "/Users/raghd/Desktop/cvg-app/CVG_CMS/tests/samples"


def http(method, url, headers=None, data=None):
    req = urllib.request.Request(url, data=data, headers=headers or {}, method=method)
    try:
        with urllib.request.urlopen(req) as r:
            return r.status, dict(r.getheaders()), r.read()
    except urllib.error.HTTPError as e:
        return e.code, dict(e.headers), e.read()


def login():
    body = json.dumps({"email": "admin@example.com", "password": "password"}).encode()
    s, _, b = http(
        "POST", f"{BASE}/auth/login",
        {"Content-Type": "application/json", "Accept": "application/json"}, body
    )
    assert s == 200, f"login failed {s} {b!r}"
    return json.loads(b)["data"]["token"]


def multipart(fields, files):
    """fields: list[(name,str)]; files: list[(name, filepath)]"""
    boundary = "----b" + uuid.uuid4().hex
    out = bytearray()
    for n, v in fields:
        out += f"--{boundary}\r\nContent-Disposition: form-data; name=\"{n}\"\r\n\r\n".encode()
        out += str(v).encode()
        out += b"\r\n"
    for n, p in files:
        fname = os.path.basename(p)
        ctype = mimetypes.guess_type(fname)[0] or "application/octet-stream"
        with open(p, "rb") as f:
            data = f.read()
        out += f"--{boundary}\r\nContent-Disposition: form-data; name=\"{n}\"; filename=\"{fname}\"\r\nContent-Type: {ctype}\r\n\r\n".encode()
        out += data + b"\r\n"
    out += f"--{boundary}--\r\n".encode()
    return bytes(out), f"multipart/form-data; boundary={boundary}"


def H(token, ctype=None):
    h = {"Authorization": f"Bearer {token}", "Accept": "application/json", "Origin": ORIGIN}
    if ctype:
        h["Content-Type"] = ctype
    return h


def main():
    # 0. CORS preflight
    s, hdrs, _ = http("OPTIONS", f"{BASE}/admin/projects/1", {
        "Origin": ORIGIN,
        "Access-Control-Request-Method": "POST",
        "Access-Control-Request-Headers": "authorization,content-type",
    })
    assert s == 204, f"preflight {s}"
    assert hdrs.get("Access-Control-Allow-Origin") == ORIGIN, hdrs
    assert hdrs.get("Access-Control-Allow-Credentials") == "true", hdrs
    print("[OK] CORS preflight 204 with proper headers")

    token = login()
    print("[OK] login")

    # category
    s, _, b = http("GET", f"{BASE}/admin/categories", H(token))
    assert s == 200
    cat_id = json.loads(b)["data"][0]["id"]

    # 1. CREATE without content (was previously blocked client-side, now allowed)
    body, ct = multipart(
        [
            ("title", "Test FullFlow A 1776967629"),
            ("description", "desc"),
            # no content field at all
            ("featured", "1"),
            ("category_id", cat_id),
        ],
        [],
    )
    s, _, b = http("POST", f"{BASE}/admin/projects", H(token, ct), body)
    assert s in (200, 201), f"create-no-content {s} {b!r}"
    pid_a = json.loads(b)["data"]["id"]
    print(f"[OK] create without content -> id {pid_a}")

    # 2. CREATE with image + video
    body, ct = multipart(
        [
            ("title", "Test FullFlow B 1776967629"),
            ("description", "desc"),
            ("content", "<p>hello</p>"),
            ("featured", "1"),
            ("category_id", cat_id),
            ("images[0][alt_text]", "img alt"),
            ("images[0][title]", "img title"),
            ("images[0][sort_order]", "1"),
            ("images[1][alt_text]", "vid alt"),
            ("images[1][title]", "vid title"),
            ("images[1][sort_order]", "2"),
        ],
        [
            ("images[0][file]", f"{SAMPLES}/img1.jpg"),
            ("images[1][file]", f"{SAMPLES}/sample.mp4"),
        ],
    )
    s, _, b = http("POST", f"{BASE}/admin/projects", H(token, ct), body)
    assert s in (200, 201), f"create-with-media {s} {b!r}"
    proj_b = json.loads(b)["data"]
    pid_b = proj_b["id"]
    assert len(proj_b["images"]) == 2
    types = sorted(i["type"] for i in proj_b["images"])
    assert types == ["image", "video"], types
    img_id, vid_id = None, None
    for i in proj_b["images"]:
        if i["type"] == "image":
            img_id = i["id"]
        else:
            vid_id = i["id"]
    print(f"[OK] create with image+video -> id {pid_b}")

    # 3. UPDATE pid_b: keep+rename video, drop image, add new image
    body, ct = multipart(
        [
            ("title", "Test FullFlow B 1776967629 updated"),
            ("description", "desc2"),
            ("content", "<p>updated</p>"),
            ("featured", "1"),
            ("category_id", cat_id),
            (f"existing_images[0][id]", str(vid_id)),
            (f"existing_images[0][sort_order]", "1"),
            (f"existing_images[0][alt_text]", "vid renamed"),
            (f"existing_images[0][title]", "vid renamed title"),
            (f"removed_image_ids[0]", str(img_id)),
            ("images[0][alt_text]", "new img"),
            ("images[0][title]", "new img"),
            ("images[0][sort_order]", "2"),
        ],
        [("images[0][file]", f"{SAMPLES}/img2.jpg")],
    )
    s, _, b = http("POST", f"{BASE}/admin/projects/{pid_b}", H(token, ct), body)
    assert s == 200, f"update-mixed {s} {b!r}"
    upd = json.loads(b)["data"]
    assert upd["title"] == "Test FullFlow B 1776967629 updated"
    assert len(upd["images"]) == 2
    kinds = sorted(i["type"] for i in upd["images"])
    assert kinds == ["image", "video"], kinds
    vid = next(i for i in upd["images"] if i["type"] == "video")
    assert vid["alt_text"] == "vid renamed"
    print("[OK] mixed update (drop, keep+rename, add) ")

    # 4. UPDATE pid_a: text-only update with NO images at all  (the user's project-2 case)
    body, ct = multipart(
        [
            ("title", "Test FullFlow A 1776967629 renamed"),
            ("description", "desc"),
            ("content", ""),  # empty allowed
            ("featured", "1"),
            ("category_id", cat_id),
        ],
        [],
    )
    s, _, b = http("POST", f"{BASE}/admin/projects/{pid_a}", H(token, ct), body)
    assert s == 200, f"update-text-only {s} {b!r}"
    print("[OK] text-only update with no images (project-2 scenario)")

    # 5. cleanup
    for pid in (pid_a, pid_b):
        s, _, _ = http("DELETE", f"{BASE}/admin/projects/{pid}", H(token))
        assert s == 200, f"delete {pid} -> {s}"
    print("[OK] delete cleanup")

    print("\nALL TESTS PASSED")


if __name__ == "__main__":
    main()
