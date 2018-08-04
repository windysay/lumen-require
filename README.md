# Lumen 生产环境集成

## 类库
- helper.php：laravel公共方法（自动加载）
    - config_path
    - public_path
    - assert
    - cache
    - request
    - logger
- 常用工具 Utils
    - Env 判断环境变量
        - isDev: 判断是否 `dev` `local`
        - isTest: 判断是否 `test`
        - isDevOrTest: 判断是否 `dev` `local` `test`
        - isProd: 判断是否 `production` `staging`

## 集成模块
- guzzlehttp/guzzle：用于发起外部请求
- illuminate/redis：laravel redis 封装
- predis/predis：predis 封装

## 计划
- [ ] 集成 `php-libs`
- [ ] 集成 `ErrorHandler`
- [ ] 集成 `Auth`
- [ ] 集成 `rbac`
- [ ] 集成 `Oss`
- [ ] 规范化目录结构
