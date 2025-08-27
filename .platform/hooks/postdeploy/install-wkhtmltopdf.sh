#!/bin/bash
set -e

echo "📦 Installing wkhtmltopdf 0.12.4 ..."

# 下載壓縮檔
cd /tmp
curl -L -o wkhtmltox.tar.xz https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz

# 解壓縮
tar -xf wkhtmltox.tar.xz

# 移動到 /usr/local
mv wkhtmltox /usr/local/wkhtmltox

# 建立 symlink，讓系統能直接找到 wkhtmltopdf
ln -sf /usr/local/wkhtmltox/bin/wkhtmltopdf /usr/bin/wkhtmltopdf
ln -sf /usr/local/wkhtmltox/bin/wkhtmltoimage /usr/bin/wkhtmltoimage

# 確認安裝成功
wkhtmltopdf --version