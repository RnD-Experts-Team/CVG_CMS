#!/usr/bin/env python3
"""End-to-end test of the multi-image project flow."""
import json
import sys
import urllib.request
from pathlib import Path

import urllib.error

BASE = "http://127.0.0.1:8000/api"
DIR = Path("/Users/raghd/Desktop/cvg-app/CVG_CMS/storage/app/test-uploads")


def request(method, path, token=None, fields=None, files=None):
    """Issue an HTTP request, optionally multipart."""
    boundary = "----CVGTestBoundary7nx8a"
    headers = {"Accept": "application/json"}
    if token:
        headers["Authorization"] = f"Bearer {token}"

    body = b""
    if fields or files:
        for name, value in (fields or []):
            body += f"--{boundary}\r\n".encode()
            body += f'Content-Disposition: form-data; name="{name}"\r\n\r\n'.encode()
            body += str(value).encode() + b"\r\n"
        for name, filepath in (files or []):
            body += f"--{boundary}\r\n".encode()
            fname = Path(filepath).name
            body += (
                f'Content-Disposition: form-data; name="{name}"; filename="{fname}"\r\n'
                f"Content-Type: image/jpeg\r\n\r\n"
            ).encode()
            body += Path(filepath).read_bytes() + b"\r\n"
        body += f"--{boundary}--\r\n".encode()
        headers["Content-Type"] = f"multipart/form-data; boundary={boundary}"

    req = urllib.request.Request(BASE + path, data=body or None, method=method, headers=headers)
    try:
        with urllib.request.urlopen(req) as r:
            return r.status, json.loads(r.read().decode() or "null")
    except urllib.error.HTTPError as e:
        return e.code, json.loads(e.read().decode() or "null")


def main():
    # Login
    code, body = request("POST", "/auth/login", fields=[("email", "admin@example.com"), ("password", "password")])
    assert code == 200, body
    token = body["data"]["token"]
    print(f"login OK token_len={len(token)}")

    # Get a category id
    code, body = request("GET", "/admin/categories", token=token)
    assert code == 200, body
    cat_id = body["data"][0]["id"]
    print(f"category_id={cat_id}")

    # CREATE with two images
    code, body = request(
        "POST", "/admin/projects", token=token,
        fields=[
            ("title", "E2E Multi Image"),
            ("description", "desc"),
            ("content", "<p>content</p>"),
            ("featured", 1),
            ("category_id", cat_id),
            ("images[0][alt_text]", "alt1"),
            ("images[0][title]", "t1"),
            ("images[0][sort_order]", 1),
            ("images[1][alt_text]", "alt2"),
            ("images[1][title]", "t2"),
            ("images[1][sort_order]", 2),
        ],
        files=[
            ("images[0][file]", DIR / "img1.jpg"),
            ("images[1][file]", DIR / "img2.jpg"),
        ],
    )
    print(f"CREATE status={code}")
    print(json.dumps(body, indent=2)[:1200])
    assert code == 201, body
    proj = body["data"]
    pid = proj["id"]
    images = proj["images"]
    assert len(images) == 2, f"expected 2 images, got {len(images)}"
    keep_id = images[1]["id"]   # the second one — we'll keep
    remove_id = images[0]["id"] # the first one — we'll remove
    print(f"created project id={pid}, keep={keep_id}, remove={remove_id}")

    # UPDATE: remove image[0], keep image[1] with edited alt, add img3
    code, body = request(
        "POST", f"/admin/projects/{pid}", token=token,
        fields=[
            ("title", "E2E Multi Image (updated)"),
            ("description", "desc updated"),
            ("content", "<p>updated</p>"),
            ("featured", 1),
            ("category_id", cat_id),
            # remove first image
            ("removed_image_ids[0]", remove_id),
            # keep the other one with edits
            ("existing_images[0][id]", keep_id),
            ("existing_images[0][sort_order]", 1),
            ("existing_images[0][alt_text]", "alt2-edited"),
            ("existing_images[0][title]", "t2-edited"),
            # add a new image
            ("images[0][alt_text]", "alt3"),
            ("images[0][title]", "t3"),
            ("images[0][sort_order]", 2),
        ],
        files=[("images[0][file]", DIR / "img3.jpg")],
    )
    print(f"UPDATE status={code}")
    assert code == 200, body
    print(json.dumps(body, indent=2)[:1500])

    # FETCH and verify
    code, body = request("GET", f"/admin/projects/{pid}", token=token)
    assert code == 200, body
    final_images = body["data"]["images"]
    print("\nFINAL IMAGES:")
    print(json.dumps(final_images, indent=2))

    assert len(final_images) == 2, f"expected 2 final images, got {len(final_images)}"
    ids = sorted(i["id"] for i in final_images)
    assert keep_id in ids, "kept image was lost"
    assert remove_id not in ids, "removed image still present"
    kept = next(i for i in final_images if i["id"] == keep_id)
    assert kept["alt_text"] == "alt2-edited", f"alt not updated: {kept}"
    print("\nALL ASSERTIONS PASSED")

    # CLEANUP
    code, body = request("DELETE", f"/admin/projects/{pid}", token=token)
    print(f"DELETE status={code}")


if __name__ == "__main__":
    main()
