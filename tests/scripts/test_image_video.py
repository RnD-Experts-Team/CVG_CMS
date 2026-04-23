#!/usr/bin/env python3
"""End-to-end test: create + update with mixed image+video gallery."""
import json, urllib.request, urllib.error
from pathlib import Path

BASE = "http://127.0.0.1:8000/api"
DIR = Path("/Users/raghd/Desktop/cvg-app/CVG_CMS/storage/app/test-uploads")

CT = {".jpg": "image/jpeg", ".jpeg": "image/jpeg", ".png": "image/png", ".mp4": "video/mp4"}


def req(method, path, token=None, fields=None, files=None):
    boundary = "----CVG7nx8a"
    headers = {"Accept": "application/json"}
    if token:
        headers["Authorization"] = f"Bearer {token}"
    body = b""
    if fields or files:
        for n, v in (fields or []):
            body += f"--{boundary}\r\nContent-Disposition: form-data; name=\"{n}\"\r\n\r\n".encode() + str(v).encode() + b"\r\n"
        for n, fp in (files or []):
            ct = CT.get(Path(fp).suffix.lower(), "application/octet-stream")
            body += (
                f"--{boundary}\r\nContent-Disposition: form-data; "
                f"name=\"{n}\"; filename=\"{Path(fp).name}\"\r\n"
                f"Content-Type: {ct}\r\n\r\n"
            ).encode() + Path(fp).read_bytes() + b"\r\n"
        body += f"--{boundary}--\r\n".encode()
        headers["Content-Type"] = f"multipart/form-data; boundary={boundary}"
    r = urllib.request.Request(BASE + path, data=body or None, method=method, headers=headers)
    try:
        with urllib.request.urlopen(r) as resp:
            return resp.status, json.loads(resp.read().decode() or "null")
    except urllib.error.HTTPError as e:
        return e.code, json.loads(e.read().decode() or "null")


def main():
    c, b = req("POST", "/auth/login", fields=[("email", "admin@example.com"), ("password", "password")])
    assert c == 200, b
    token = b["data"]["token"]

    c, b = req("GET", "/admin/categories", token=token)
    cat_id = b["data"][0]["id"]

    # CREATE with 1 image + 1 video
    c, b = req(
        "POST", "/admin/projects", token=token,
        fields=[
            ("title", "Mixed Media Project"),
            ("description", "image+video"),
            ("content", "<p>x</p>"),
            ("featured", 1),
            ("category_id", cat_id),
            ("images[0][alt_text]", "img"),
            ("images[0][title]", "image"),
            ("images[0][sort_order]", 1),
            ("images[1][alt_text]", "vid"),
            ("images[1][title]", "video"),
            ("images[1][sort_order]", 2),
        ],
        files=[
            ("images[0][file]", DIR / "img1.jpg"),
            ("images[1][file]", DIR / "sample.mp4"),
        ],
    )
    print(f"CREATE -> {c}")
    assert c == 201, json.dumps(b, indent=2)
    proj = b["data"]
    pid = proj["id"]
    images = proj["images"]
    print(json.dumps(images, indent=2))
    assert len(images) == 2
    types = sorted(i["type"] for i in images)
    assert types == ["image", "video"], f"expected image+video, got {types}"
    img_row = next(i for i in images if i["type"] == "image")
    vid_row = next(i for i in images if i["type"] == "video")
    assert img_row["width"] and img_row["height"], f"image missing dims: {img_row}"
    assert vid_row["width"] in (None, 0) or vid_row["width"] is None, f"video should not have width: {vid_row}"
    assert vid_row["mime_type"] == "video/mp4", f"video mime wrong: {vid_row}"
    print("CREATE assertions passed")

    # UPDATE: keep video, remove image, add a new image
    c, b = req(
        "POST", f"/admin/projects/{pid}", token=token,
        fields=[
            ("title", "Mixed Media Project (v2)"),
            ("description", "updated"),
            ("content", "<p>x</p>"),
            ("featured", 1),
            ("category_id", cat_id),
            ("removed_image_ids[0]", img_row["id"]),
            ("existing_images[0][id]", vid_row["id"]),
            ("existing_images[0][sort_order]", 1),
            ("existing_images[0][alt_text]", "video updated"),
            ("existing_images[0][title]", "video updated"),
            ("images[0][alt_text]", "img2"),
            ("images[0][title]", "img2"),
            ("images[0][sort_order]", 2),
        ],
        files=[("images[0][file]", DIR / "img2.jpg")],
    )
    print(f"UPDATE -> {c}")
    assert c == 200, json.dumps(b, indent=2)

    # Verify final state
    c, b = req("GET", f"/admin/projects/{pid}", token=token)
    final = b["data"]["images"]
    print("FINAL:")
    print(json.dumps(final, indent=2))
    assert len(final) == 2, f"expected 2 final, got {len(final)}"
    assert any(i["id"] == vid_row["id"] and i["alt_text"] == "video updated" for i in final), "video not updated"
    assert all(i["id"] != img_row["id"] for i in final), "removed image still present"
    print("UPDATE assertions passed")

    # INVALID file type
    bad = DIR / "bad.txt"
    bad.write_text("hello")
    c, b = req(
        "POST", f"/admin/projects/{pid}", token=token,
        fields=[
            ("title", "x"), ("description", "x"), ("content", "x"),
            ("featured", 1), ("category_id", cat_id),
            ("images[0][alt_text]", "x"), ("images[0][title]", "x"), ("images[0][sort_order]", 1),
        ],
        files=[("images[0][file]", bad)],
    )
    print(f"BAD MIME -> {c}: {b}")
    assert c == 422, "expected 422 on bad mime"
    print("VALIDATION assertion passed")

    # Cleanup
    c, _ = req("DELETE", f"/admin/projects/{pid}", token=token)
    print(f"DELETE -> {c}")
    print("\nALL TESTS PASSED")


if __name__ == "__main__":
    main()
