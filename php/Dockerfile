# 使用官方 PHP 镜像
FROM php:8.3-cli
# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /app

# 复制 composer.json 和 composer.lock
COPY composer.json composer.lock ./

# 安装生产依赖
RUN composer install --no-dev --no-progress --no-interaction

# 复制项目文件
COPY . .

# 安装 PHPUnit 作为开发依赖项
RUN COMPOSER_MEMORY_LIMIT=-1 composer require --dev phpunit/phpunit --no-progress --no-interaction

# 运行单元测试
CMD ["vendor/bin/phpunit"]