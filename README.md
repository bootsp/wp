# 网盘资源项目说明

主要文件和目录：

## 目录结构

- `Dockerfile`  
  基于 `php:7.4-alpine` 的 Docker 镜像构建文件，适用于快速搭建 PHP 运行环境。

- `index.php`  
  入口文件，通常为自定义功能或页面的主访问入口。

- `login_config.php`  
  登录相关配置文件，包含登录逻辑所需的配置信息。

- `login.php`  
  登录页面或处理登录请求的脚本。

- `data/`  
  数据目录，用于存放运行过程中产生的数据文件或缓存。

## 快速开始

1. **构建 Docker 镜像**

   在本目录下执行以下命令：

   ```bash
   docker build -t wp-app .

2.访问管理后台

IP或者域名/?admin

密码默认123456
