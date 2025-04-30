# Сервис уведомлений через Telegram-бота

Сервис на Laravel для получения задач из внешнего API и рассылки уведомлений пользователям в Telegram.

## Требования

- Docker
- Docker Compose

## Установка и запуск

1. Клонируйте репозиторий:
```bash
git clone https://github.com/santaklouse/telegram-notifier-test-assignment.git
cd telegram-notifier-test-assignment
```

2. Создайте файл .env:
```bash
cp .env.example .env
```

- Обновите файл .env, добавив свои данные для Telegram-бота:

```yaml
TELEGRAM_BOT_TOKEN=your_bot_token
```

- добавьте свой токен ngrok в .env файл:

```yaml
NGROK_AUTH_TOKEN=your_ngrok_auth_token
```

- создайте ngrok домен и установите его как переменную APP_URL (https://dashboard.ngrok.com/domains)

```yaml
APP_URL=https://intimate-anteater-primary.ngrok-free.app
```

```bash
composer install
```

4. Запустите контейнеры с помощью Laravel Sail:
```bash
make up
```

5. Установите зависимости и выполните миграции:
```bash
./sail composer install
./sail artisan key:generate
./sail artisan telegram:set-webhook-endpoint
./sail artisan migrate 

```

- установите webhook для вашего бота, используя команду:
```bash
./vendor/bin/sail artisan telegram:set-webhook-endpoint
```

## Использование

### Telegram-бот

1. Отправьте команду `/start` боту для регистрации
2. Отправьте команду `/stop` для отмены подписки на уведомления

### Консольные команды

Для обработки очереди задач, вы можете использовать консольную команду:

```bash 
./sail artisan queue:work
```

Для запуска консольной команды, которая получает задачи и отправляет уведомления:

```bash
./sail artisan notify-tasks
```

## Тесты

Для запуска тестов:

```bash
make test
```

## API Документация

Swagger документация доступна по адресу:
```
http://localhost/api/documentation
```

## Структура проекта

- `app/Models/User.php` - Модель пользователя
- `app/Console/Commands/NotifyTasks.php` - Консольная команда для получения задач и отправки уведомлений
- `app/Jobs/SendTelegramNotification.php` - Job для отправки уведомлений через очередь
- `app/Services/TelegramService.php` - Сервис для работы с Telegram API
- `app/Services/TaskService.php` - Сервис для получения задач из внешнего API
