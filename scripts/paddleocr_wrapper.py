#!/usr/bin/env python3
"""
PaddleOCR Python wrapper script for the Laravel Extraction Pipeline.

Called by PHP's PaddleOcrEngine via Process::run().
Accepts an image/PDF path and language, outputs JSON with extracted text and confidence.

Usage:
    python paddleocr_wrapper.py --image <path> --lang <en|hi> [--use_angle_cls]

Output (stdout):
    JSON object: {"text": "...", "confidence": 0.95, "lines": [...]}
"""

import argparse
import json
import sys
import os


def main():
    parser = argparse.ArgumentParser(description="PaddleOCR wrapper for PHP integration")
    parser.add_argument("--image", required=True, help="Path to the image or PDF file")
    parser.add_argument("--lang", default="en", help="Language code: en, hi, or en+hi for mixed")
    parser.add_argument("--use_angle_cls", action="store_true", default=True, help="Enable angle classification")
    parser.add_argument("--pages", default=None, help="Comma-separated page numbers (1-indexed) for PDF")
    args = parser.parse_args()

    if not os.path.exists(args.image):
        print(json.dumps({"error": f"File not found: {args.image}", "text": "", "confidence": 0}))
        sys.exit(1)

    try:
        from paddleocr import PaddleOCR

        # Map language codes
        lang = args.lang
        if lang == "en+hi" or lang == "mixed":
            lang = "hi"  # PaddleOCR's Hindi model handles Devanagari + Latin mixed text

        ocr = PaddleOCR(
            use_angle_cls=args.use_angle_cls,
            lang=lang,
            show_log=False,
        )

        # Handle page selection for multi-page PDFs
        page_num = None
        if args.pages:
            page_num = [int(p) - 1 for p in args.pages.split(",")]  # Convert 1-indexed to 0-indexed

        result = ocr.ocr(args.image, cls=args.use_angle_cls)

        if result is None:
            print(json.dumps({"text": "", "confidence": 0, "lines": [], "error": "OCR returned no results"}))
            sys.exit(0)

        all_lines = []
        all_confidences = []

        for page in result:
            if page is None:
                continue
            for line in page:
                if line and len(line) >= 2:
                    text_info = line[1]
                    if isinstance(text_info, tuple) and len(text_info) >= 2:
                        text = text_info[0]
                        confidence = float(text_info[1])
                        all_lines.append(text)
                        all_confidences.append(confidence)

        combined_text = "\n".join(all_lines)
        avg_confidence = (sum(all_confidences) / len(all_confidences) * 100) if all_confidences else 0.0

        output = {
            "text": combined_text,
            "confidence": round(avg_confidence, 2),
            "lines": all_lines,
            "line_count": len(all_lines),
        }

        print(json.dumps(output, ensure_ascii=False))

    except ImportError as e:
        print(json.dumps({"error": f"PaddleOCR not installed: {str(e)}", "text": "", "confidence": 0}))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({"error": str(e), "text": "", "confidence": 0}))
        sys.exit(1)


if __name__ == "__main__":
    main()
