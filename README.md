Hello future teammate!
==========
php 生成镜像命令：
sudo docker build -t 20250223-image .
生成容器并且进入容器命令：
docker run -it <image_name> /bin/bash
docker run -it 20250223-image /bin/bash
进入容器后执行命令：
./vendor/bin/phpunit ./tests/Src/MyGreeterTest.php





# 使用官方 PHP 镜像
FROM php:8.3-cli

# 安装必要的 PHP 扩展
RUN docker-php-ext-install mbstring xml pdo_mysql

# 安装 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 设置 Composer 镜像源
RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

# 设置工作目录
WORKDIR /app

# 复制项目文件
COPY . .

# 解除内存限制
ENV COMPOSER_MEMORY_LIMIT=-1

# 安装依赖
RUN composer install --no-dev --optimize-autoloader

# 安装 PHPUnit
RUN composer require --dev phpunit/phpunit --no-progress --no-interaction

# 运行单元测试
CMD ["phpunit"]