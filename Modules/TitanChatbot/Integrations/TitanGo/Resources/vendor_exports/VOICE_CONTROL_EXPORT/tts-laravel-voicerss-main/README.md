# 🎙️ Laravel Voice RSS - Text to Speech

Este projeto é uma aplicação simples desenvolvida em **Laravel 12 + PHP 8.4** que converte texto em voz utilizando a **API Voice RSS**.

---

## 🚀 Funcionalidades
- Formulário para digitar texto.
- Seleção de idioma (pt-BR, en-US, es-MX).
- Integração com API externa (Voice RSS).
- Retorno em áudio MP3 com player embutido.
- Opção para baixar o arquivo de áudio gerado.

---

## 🛠️ Tecnologias
- **Laravel 12.37.0**
- **PHP 8.4.14**
- **Voice RSS API**
- **Guzzle HTTP Client**
- **Blade Templates**

---

## ⚙️ Instalação

```bash
# Clonar o repositório
git clone https://github.com/seuusuario/tts-voicerss.git

cd tts-voicerss

# Instalar dependências do Laravel
composer install

# Gerar chave da aplicação
cp .env.example .env
php artisan key:generate

# Configurar o arquivo .env
# (adicione sua chave Voice RSS)
VOICERSS_KEY=sua_chave_aqui

# Criar link simbólico de storage
php artisan storage:link
