import argparse
import json
import os
import sys

from paddleocr import PaddleOCR


def build_ocr(lang: str, use_gpu: bool):
    return PaddleOCR(
        use_angle_cls=True,
        lang=lang,
        use_gpu=use_gpu,
        show_log=False,
    )


def normalize_lines(result):
    lines = []
    confidences = []

    if not result:
        return "", 0, 0.0

    for page in result:
        if not page:
            continue

        for item in page:
            if not item or len(item) < 2:
                continue

            rec = item[1]
            if not rec or len(rec) < 2:
                continue

            text = str(rec[0]).strip()
            try:
                score = float(rec[1])
            except Exception:
                score = 0.0

            if not text:
                continue

            lines.append(text)
            confidences.append(score)

    joined = "\n".join(lines).strip()
    avg_conf = round(sum(confidences) / len(confidences), 4) if confidences else 0.0

    return joined, len(lines), avg_conf


def run_ocr(file_path, lang, use_gpu):
    ocr = build_ocr(lang=lang, use_gpu=use_gpu)
    return ocr.ocr(file_path, cls=True)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--file", required=True)
    parser.add_argument("--lang", default="en")
    parser.add_argument("--use-gpu", default="0")
    parser.add_argument("--enable-mkldnn", default="1")
    parser.add_argument("--det-limit-side-len", default="1216")
    parser.add_argument("--ocr-version", default="PP-OCRv4")
    parser.add_argument("--mime-type", default="")
    args = parser.parse_args()

    file_path = args.file
    lang = args.lang or "en"
    use_gpu = args.use_gpu == "1"

    if not os.path.isfile(file_path):
        print(json.dumps({
            "success": False,
            "message": f"File tidak ditemukan: {file_path}"
        }, ensure_ascii=False))
        sys.exit(0)

    try:
        try:
            result = run_ocr(file_path, lang, use_gpu)
            used_lang = lang
        except Exception:
            fallback_lang = "en"
            result = run_ocr(file_path, fallback_lang, use_gpu)
            used_lang = fallback_lang

        text, line_count, avg_confidence = normalize_lines(result)

        print(json.dumps({
            "success": True,
            "message": "OCR berhasil",
            "text": text,
            "meta": {
                "line_count": line_count,
                "avg_confidence": avg_confidence,
                "engine": "paddleocr",
                "used_lang": used_lang
            }
        }, ensure_ascii=False))
    except Exception as e:
        print(json.dumps({
            "success": False,
            "message": str(e)
        }, ensure_ascii=False))


if __name__ == "__main__":
    main()