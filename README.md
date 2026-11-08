<p align="center">
    <a href="#" target="_blank">
        <img src="https://drive.google.com/uc?export=download&id=1eCCIrPRB9jp3qjfr1jzjHtz2qqnkGC2k" width="300" alt="Logo Linhir">
    </a>
</p>

# ⚔️ Linhir - Sistema de Gestión para Gremios de Albion Online

**Linhir** es una plataforma web integral desarrollada para optimizar la gestión y la comunidad del gremio del mismo nombre en *Albion Online* (Servidor West, ciudad de Fort Sterling). El sistema integra sincronización en tiempo real con la API oficial del juego, análisis profundo de batallas, gestión financiera, autenticación con Discord y herramientas administrativas avanzadas para los oficiales del gremio.

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Livewire Version](https://img.shields.io/badge/Livewire-3.x-4e56a6.svg)](https://livewire.laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-^8.2-777bb4.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38bdf8.svg)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

---

## 📋 Tabla de Contenidos

- [Características Principales](#-características-principales)
- [Inteligencia y Análisis Bélico](#-inteligencia-y-análisis-bélico)
- [Módulos de Gestión](#-módulos-de-gestión)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Comandos Programados](#-comandos-programados)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Autores](#-autores)

---

## 🚀 Características Principales

*   **🔐 Autenticación Multi-Vía**: Inicio de sesión tradicional (email/contraseña) o mediante **Discord OAuth2** (Socialite).
*   **🤖 Integración Total con Discord**: Envío de reportes de batalla, gestión de roles automáticos (Oficial/Miembro), notificaciones de blog, estado del servidor y alertas de cumpleaños a través de Webhooks y la API del Bot.
*   **🔄 Sincronización Automática (Albion API)**: Consumo de la API de `gameinfo.albiononline.com` para actualizar miembros, batallas, precio del oro, y estadísticas de por vida (PVE, PVP, Recolección, Crafteo).
*   **🏆 Sistema de Puntos y Tareas**: Los oficiales pueden crear tareas. Los usuarios las completan, envían evidencias y los oficiales las aprueban. Todo se refleja en un ranking de puntos.
*   **🎯 Gestión de CTAs (Call to Action)**: Creación de actividades desde la web o el bot, asignación de armas específicas por composición (con soporte para emojis de Discord), y publicación automática con botones interactivos en Discord.
*   **🏦 Módulo Bancario**: Lleva el control del ledger del gremio (ingresos/egresos), el saldo personal de cada miembro y gestiona las solicitudes de retiro de los mismos.

---

## 🛡️ Inteligencia y Análisis Bélico (War Room)

El sistema cuenta con un ecosistema avanzado para analizar el rendimiento en PvP:

1.  **Listado de Batallas y Detalle**: Análisis de kills, daño, curación y desglose por gremio/alianza.
2.  **Equipamiento Perdido (Gear Loss Tracker)**: Extrae de los `KillEvents` qué ítems exactos perdió cada miembro de Linhir en una batalla, agrupándolos con sus íconos y cantidades (Tier y Encantamiento).
3.  **Reporte Multi-Batalla (Campañas)**: Selección manual de múltiples reportes para concatenarlos. Deduplica jugadores (por su `player_id`), sumando sus kills y muertes a lo largo de varios días o sesiones, calculando bajas-únicas vs muertes-evento.
4.  **Análisis de Guerra H2H (Head-to-Head)**: Panel para seleccionar un rango de fechas y un gremio enemigo. Cruza los `KillEvents` para determinar:
    *   Kills de Linhir vs Kills del Enemigo.
    *   Avg IP en el intercambio.
    *   Top Asesinos de ambos lados.
    *   "Piñatas" (miembros de Linhir que más mueren) y las víctimas favoritas del enemigo.
5.  **Envío a Discord**: Cualquier reporte multi-batalla o análisis puede ser enviado automáticamente como un *Embed* formateado a un canal de Discord.

---

## 🧩 Módulos de Gestión

### 📊 Dashboard Administrativo (`/admin/dashboard`)
Panel central para oficiales con KPIs en tiempo real:
*   Precio del oro, Banco Gremial, y K/D semanal del gremio.
*   Total de integrantes, porcentaje de vinculación Web/Discord y último miembro en ingresar.
*   Top Recolector de la semana (basado en `EspecialidadSemanal`).
*   Accesos rápidos a Próximas CTAs, Reportes de Tareas pendientes y Solicitudes de Retiro.

### 💼 Módulo de Administración y Comunidad
*   **Linhir (Miembros)**: Tabla completa de miembros, fama, saldo, vinculación, y edición de cumpleaños.
*   **Usuarios y Roles**: CRUD completo con sistema de permisos (Spatie).
*   **Discord Server**: Visualización de miembros del servidor, roles y vinculación con la web.
*   **Gestión de Tareas**: Crear, editar y desactivar tareas de la comunidad.

### 📝 Blog Integrado
*   Integración con la API de Google Blogger. Los artículos se renderizan en la web con la paleta de colores de Linhir y caché inteligente para optimizar el rendimiento.

### 🧮 Calculadora de Cultivos
*   Herramienta interactiva para calcular el retorno de inversión (profit) de cultivos según premium, foco, bonificaciones de ciudad y precios de mercado.

---

## 🛠️ Tecnologías Utilizadas

*   **Backend**: PHP 8.2, Laravel 12.
*   **Frontend Dinámico**: Livewire 3 (componentes reactivos sin JavaScript complejo).
*   **Autenticación**: Laravel Jetstream + Fortify + Socialite (Discord).
*   **Base de Datos**: MySQL / MariaDB (con soporte para SQLite en testing).
*   **Estilos**: Tailwind CSS 3.
*   **Permisos**: Spatie Laravel-Permission.
*   **HTTP Client**: GuzzleHttp para consumir APIs externas (Albion, Discord).
*   **Colas (Queues)**: Procesamiento asíncrono para batallas (`ProcessBattle`) y recordatorios de CTAs.
*   **IA (Opcional)**: Integración con Ollama (LLaMA3) para asistente interno.

---

## ⏱️ Comandos Programados

El sistema utiliza el programador de tareas de Laravel (`routes/console.php`):

| Comando | Frecuencia | Descripción |
| :--- | :--- | :--- |
| `app:integrantes-de-linhir` | Cada hora | Sincroniza miembros y estadísticas de por vida (PVE, PVP, etc.). |
| `app:sync-battles` | Cada 30 min | Busca y encola nuevas batallas para procesarlas. |
| `app:update-especialidades-semanales` | Cada hora | Calcula la ganancia semanal de fama por especialidad. |
| `app:fama_update` | Cada hora | Actualiza la fama PvE y PvP semanal. |
| `app:update-gold-price` | Cada hora | Guarda el precio actual del Oro. |
| `app:server_check` | 15 segs (Rango) | Monitorea el estado del servidor y alerta a Discord. |
| `ctas:send-reminders` | Cada minuto | Envía DMs en Discord a los inscritos a CTAs próximas. |
| `discord:birthday-notification` | Diario | Felicita a los miembros en Discord por su cumpleaños. |
| `blog:check-new-posts` | Cada 30 min | Notifica a Discord sobre nuevos artículos en el blog. |
| `sitemap:generate` | Semanal | Genera el `sitemap.xml` para SEO. |

---

## 📁 Estructura del Proyecto

*   `app/Console/Commands/`: Comandos Artisan para sincronización y automatización.
*   `app/Jobs/`: Procesamiento en cola (ej. `ProcessBattle` para extraer equipamiento y kills).
*   `app/Livewire/`: Componentes reactivos.
    *   `Modulo/Battles/`: Batallas, Multi-Batalla, Análisis de Guerra.
    *   `Modulo/Admin/`: Dashboards y gestión de Discord/Usuarios.
    *   `Modulo/Banco/`: Cuentas y retiros.
    *   `Modulo/Ctas/`: Composiciones, creación y gestión de actividades.
*   `app/Models/`: Modelos Eloquent (Battle, KillEvent, Personaje, Cta, etc.).
*   `app/Services/`: Lógica de negocio (Discord, GuildBank, Tasks, CtaEmbedBuilder).
*   `app/Traits/`: Integraciones con APIs (Albion, Discord).

---

## 👥 Autor

*   **Kaizerenrique** - *Desarrollador Principal / Fundador* - [GitHub](https://github.com/kaizerenrique)

---

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia **MIT**. Si te resulta útil, ¡no dudes en darle una estrella ⭐ al repositorio o contribuir!

---

> **Linhir** - *"Un gremio de crafters y farmers en Albion Online"*