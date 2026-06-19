"""Resize generated images to WordPress.org SVN asset dimensions."""
from PIL import Image
from pathlib import Path

SRC = Path(__file__).resolve().parent / "_source"

DST = Path(__file__).resolve().parent


def resize_cover(img: Image.Image, size: tuple[int, int]) -> Image.Image:
    """Crop-resize to exact dimensions."""
    if img.mode in ("P", "LA", "RGBA"):
        img = img.convert("RGBA")
    else:
        img = img.convert("RGB")

    target_w, target_h = size
    src_w, src_h = img.size
    scale = max(target_w / src_w, target_h / src_h)
    new_w, new_h = int(src_w * scale), int(src_h * scale)
    resized = img.resize((new_w, new_h), Image.Resampling.LANCZOS)
    left = (new_w - target_w) // 2
    top = (new_h - target_h) // 2
    return resized.crop((left, top, left + target_w, top + target_h))


def main() -> None:
    DST.mkdir(parents=True, exist_ok=True)

    banner = Image.open(SRC / "banner-772x250.png")
    resize_cover(banner, (772, 250)).save(DST / "banner-772x250.png", "PNG", optimize=True)
    resize_cover(banner, (1544, 500)).save(DST / "banner-1544x500.png", "PNG", optimize=True)

    icon = Image.open(SRC / "icon-256x256.png")
    resize_cover(icon, (256, 256)).save(DST / "icon-256x256.png", "PNG", optimize=True)
    resize_cover(icon, (128, 128)).save(DST / "icon-128x128.png", "PNG", optimize=True)

    for i in range(1, 6):
        shot_path = SRC / f"screenshot-{i}.png"
        if not shot_path.exists():
            continue
        shot = Image.open(shot_path)
        w, h = shot.size
        if w > 1280:
            new_h = int(h * (1280 / w))
            shot = shot.resize((1280, new_h), Image.Resampling.LANCZOS)
        shot.convert("RGB").save(DST / f"screenshot-{i}.png", "PNG", optimize=True)

    for f in sorted(DST.glob("*.png")):
        with Image.open(f) as img:
            print(f"{f.name}: {f.stat().st_size // 1024} KB, {img.size}")


if __name__ == "__main__":
    main()
