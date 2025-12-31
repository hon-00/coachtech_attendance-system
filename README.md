# coachtech_attendance-system

## アプリ概要
一般ユーザーと管理者ユーザーを持つ勤怠管理アプリケーションです。  
一般ユーザーは勤怠の打刻・確認・修正申請を行い、管理者は勤怠・スタッフ・修正申請を管理および承認します。

---

## 環境構築
- git clone git@github.com:hon-00/coachtech_attendance-system.git
- cd coachtech_attendance-system
- docker-compose up -d --build

## Laravel環境構築
- docker-compose exec php bash
※ 以降の作業は PHP コンテナ内で実行します。
  Laravel プロジェクトは src ディレクトリ配下に構築されており、PHPコンテナ内では /var/www/html にマウントされています。

- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate:fresh --seed

## 開発環境確認
- 一般ユーザー/ログイン画面: http://localhost/login
- ログイン後初期表示（勤怠登録画面）: /attendance
- 管理者ユーザー/ログイン画面: http://localhost/admin/login
- ログイン後初期表示（勤怠一覧画面）: /admin/attendance/list
- phpMyAdmin: http://localhost:8080/

## 使用技術（実行環境）
- PHP 8.1.34
- Laravel 8.83.29
- MySQL 8.0.26
- Nginx 1.21.1
- Composer 2.x
- Docker / docker-compose

## ER図
<img width="1298" height="1590" alt="勤怠管理 drawio" src="https://github.com/user-attachments/assets/21e4ed72-348a-4549-8369-c8112933b8e9" />
