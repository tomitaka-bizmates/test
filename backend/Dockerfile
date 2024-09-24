# ベースイメージとして公式のPHPイメージを使用
FROM php:8.2-fpm

# 作業ディレクトリを設定
WORKDIR /var/www

# システムパッケージをインストール
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev

# PHP拡張をインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# `php.ini`ファイルをコピー
COPY php.ini /usr/local/etc/php/php.ini

# アプリケーションのソースをコピー
COPY . /var/www

# アプリケーションの依存関係をインストール
RUN composer install --optimize-autoloader --no-dev

# パーミッションを設定
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# ポートを公開
EXPOSE 9000

# 起動コマンド
CMD ["php-fpm"]
