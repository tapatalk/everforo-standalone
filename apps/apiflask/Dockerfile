FROM hansen1416/selenium

RUN pip install fake_useragent

WORKDIR /app

CMD ["gunicorn", "-w", "2", "--bind", "0.0.0.0", "--reload", "wsgi:app"]