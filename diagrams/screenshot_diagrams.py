"""
Screenshot semua diagram dari DIAGRAM_SIPADES.html dan simpan sebagai JPEG
"""
import asyncio
import os
from pathlib import Path
from playwright.async_api import async_playwright

BASE_URL = "http://127.0.0.1:8080/DIAGRAM_SIPADES.html"
OUTPUT_DIR = r"d:\rancangan desa\diagrams\jpeg"

SECTIONS = [
    ("flowchart",   "01_Flowchart_SIPADES.jpg",        "#flowchart"),
    ("usecase",     "02_UseCase_SIPADES.jpg",           "#usecase"),
    ("activity",    "03_Activity_SIPADES.jpg",          "#activity"),
    ("dfd0",        "04_DFD_Level0_SIPADES.jpg",        "#dfd0"),
    ("dfd1",        "05_DFD_Level1_SIPADES.jpg",        "#dfd1"),
    ("dfd2",        "06_DFD_Level2_SIPADES.jpg",        "#dfd2"),
    ("erd",         "07_ERD_SIPADES.jpg",               "#erd"),
    ("class",       "08_ClassDiagram_SIPADES.jpg",      "#class"),
    ("sequence",    "09_SequenceDiagram_SIPADES.jpg",   "#sequence"),
    ("ui",          "10_DesainUI_SIPADES.jpg",          "#ui"),
]

async def screenshot_diagrams():
    os.makedirs(OUTPUT_DIR, exist_ok=True)

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page(viewport={"width": 1600, "height": 900})

        print(f"Opening {BASE_URL} ...")
        await page.goto(BASE_URL, wait_until="networkidle", timeout=60000)
        # Wait extra time for Mermaid to render
        await page.wait_for_timeout(5000)
        print("Page loaded. Starting screenshots...")

        for section_id, filename, hash_anchor in SECTIONS:
            out_path = os.path.join(OUTPUT_DIR, filename)
            try:
                # Navigate to section
                await page.goto(BASE_URL + hash_anchor, wait_until="networkidle", timeout=30000)
                await page.wait_for_timeout(3000)  # Wait for scroll + render

                # Try to find the diagram card for this section
                # The diagram cards have id matching the section
                selector = f"#{section_id}"
                el = page.locator(selector)
                count = await el.count()
                if count > 0:
                    await el.screenshot(path=out_path, type="jpeg", quality=95)
                    print(f"  [OK] {filename} — from element #{section_id}")
                else:
                    # Fallback: full page screenshot
                    await page.screenshot(path=out_path, type="jpeg", quality=90, full_page=False)
                    print(f"  [OK] {filename} — fallback full page (element #{section_id} not found)")
            except Exception as e:
                print(f"  [ERR] {filename}: {e}")

        await browser.close()
        print(f"\nSelesai! File JPEG disimpan di: {OUTPUT_DIR}")

if __name__ == "__main__":
    asyncio.run(screenshot_diagrams())
