# 选择轻量级基础镜像
FROM php:7.4-alpine

# 复制项目文件
COPY . /var/www/html

# 设置工作目录
WORKDIR /var/www/html

# 清理不必要的文件

# 暴露端口
EXPOSE 80

# 设置启动命令
CMD ["php", "-S", "0.0.0.0:80"]