Hello future teammate!
==========
php 生成镜像命令：
sudo docker build -t 20250223-image .
生成容器并且进入容器命令：
docker run -it <image_name> /bin/bash
docker run -it 20250223-image /bin/bash
进入容器后执行命令：
./vendor/bin/phpunit ./tests/Src/MyGreeterTest.php