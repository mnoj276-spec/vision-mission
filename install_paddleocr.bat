@echo off
echo Creating Python 3.12 Virtual Environment for PaddleOCR...
python -m uv venv paddleocr_env --python 3.12

echo Installing PaddlePaddle and PaddleOCR...
set UV_HTTP_TIMEOUT=1200
python -m uv pip install paddlepaddle paddleocr --python paddleocr_env\Scripts\python.exe

echo Done.
