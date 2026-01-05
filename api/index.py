from http.server import BaseHTTPRequestHandler


# https://github.com/vercel/examples/blob/main/python/hello-world/api/index.py
class handler(BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200)
        self.send_header("Content-type", "text/plain")
        self.end_headers()
        self.wfile.write(b"Hello, world!")
        return
