# 🎵 Backend - Asociación Blend (Jukebox)

Este proyecto desarrolla la arquitectura Backend completa para el reproductor musical de la Asociación Blend, cumpliendo con los resultados de aprendizaje (Desarrollo Web en Entorno Servidor).

## 🚀 Arquitectura y Tecnologías
El proyecto evoluciona desde un servidor básico hasta una arquitectura multicontenedor profesional:
* **Etapa Inicial (Puerto 8090):** Servidor `php:8.5-apache-bookworm` básico.
* **Etapa Avanzada (Puerto 8091):** Arquitectura orquestada con **Docker Compose** que incluye:
  * Servidor Web (Apache + PHP configurado a medida mediante `Dockerfile`).
  * Base de Datos 1: **PostgreSQL 17** (`blend_pg`).
  * Base de Datos 2: **MariaDB 11.4** (`blend_maria`).

## 🛡️ Características de Seguridad Implementadas
* **Protección CSRF:** Todos los formularios validan tokens criptográficos generados dinámicamente mediante `hash_equals`.
* **Gestión de Sesiones:** Autenticación de usuarios con regeneración de ID (`session_regenerate_id`) para evitar fijación de sesiones.
* **Cifrado de Contraseñas:** Uso de `password_hash` (Bcrypt) y `password_verify`. Jamás se almacenan contraseñas en texto plano.
* **Aislamiento de Datos:** Uso de `DocumentRoot` personalizado en Apache para ocultar la carpeta `data/` del alcance público.
* **Prevención SQL Injection:** Uso exclusivo del estándar **PDO** con sentencias preparadas (`prepare` y `execute`).
* **Concurrencia de Archivos:** Implementación de pestillos de bloqueo (`flock` con `LOCK_EX`) en las operaciones CRUD basadas en JSON.

## ⚙️ Instalación y Despliegue 

1. Clonar este repositorio.
2. Navegar a la carpeta `docker/`.
3. Crear un archivo `.env` utilizando como plantilla el archivo `.env.example` proporcionado (rellenando las credenciales locales ficticias).
4. Levantar la infraestructura mediante Docker:
   ```bash
   docker compose up -d --build