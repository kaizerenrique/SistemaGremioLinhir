<p align="center">
    <a href="#" target="_blank">
        <img src="https://drive.google.com/uc?export=download&id=1eCCIrPRB9jp3qjfr1jzjHtz2qqnkGC2k" width="300" alt="Logo Linhir">
    </a>
</p>

# ⚔️ Linhir - Sistema de Gestión para Gremios de Albion Online

**Linhir** es una plataforma web integral desarrollada para optimizar la gestión y la comunidad del gremio del mismo nombre en *Albion Online* (Servidor West, ciudad de Fort Sterling). El sistema integra sincronización con la API oficial del juego, autenticación moderna, estadísticas en tiempo real, ranking de competencias semanales y herramientas administrativas avanzadas.

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Livewire Version](https://img.shields.io/badge/Livewire-3.x-4e56a6.svg)](https://livewire.laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-^8.2-777bb4.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

---

## 📋 Tabla de Contenidos

- [Características Principales](#-características-principales)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Módulos y Funcionalidades](#-módulos-y-funcionalidades)
- [Comandos Programados](#-comandos-programados)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Autores](#-autores)

---

## 🚀 Características Principales

*   **🔐 Autenticación Multi-Vía**: Inicio de sesión tradicional (email/contraseña) o mediante **Discord OAuth2** (Socialite), con generación y envío automático de contraseña por correo para nuevos usuarios.
*   **👥 Gestión de Usuarios y Roles**: Sistema robusto de permisos (Spatie) que permite controlar el acceso a módulos administrativos como "Usuarios", "Roles" y "Miembros".
*   **🔄 Sincronización Automática con Albion Online**: Consume la API pública (`gameinfo.albiononline.com`) para mantener actualizados los datos de personajes, gremios, estadísticas de por vida (PVE, PVP, Crafteo) y estadísticas de recolección (Fiber, Hide, Ore, Rock, Wood).
*   **🏆 Ranking Semanal de Especialidades**: Calcula la **fama ganada en la semana actual** para cada especialidad (Peletero, Minero, Cosechador, Leñador, Cantero, Pescador, Crafter y Agricultor). Fomenta la competencia sana al mostrar al líder de la semana en la página principal.
*   **📊 Top de Fama Semanal**: Muestra el Top 3 de jugadores con mayor ganancia de fama PvE y PvP durante la semana.
*   **🖥️ Monitorización del Servidor**: Verifica el estado del servidor de Albion cada 15 segundos (en horario específico) y envía notificaciones automáticas a Discord cuando hay cambios (Online/Offline).
*   **💰 Economía en Tiempo Real**: Consulta y almacena el precio del Oro en el mercado, visible en el panel de usuario.
*   **🎂 Sistema de Cumpleaños**: Registra fechas de cumpleaños de los miembros y envía un mensaje automático con webhook a Discord el día indicado.
*   **🏦 Módulo Bancario**: Lleva un registro de ingresos y egresos de los personajes, con filtros y saldo actualizado.
*   **🧮 Calculadora de Cultivos**: Herramienta para jugadores que calcula el retorno de inversión (profit) de cultivos según premium, foco, bonificaciones y precios de mercado.
*   **📝 Blog Integrado con Blogger**: Publica noticias, guías y novedades usando la plataforma Blogger (Google). Los artículos se muestran en la web de Linhir con el mismo diseño y paleta de colores, manteniendo la independencia del sistema principal. El contenido se cachea para un rendimiento óptimo.
*   **🔔 Notificaciones Automáticas a Discord**: Un comando programado detecta automáticamente cuando se publica un nuevo artículo en el blog y envía una notificación con embed al canal de Discord del gremio, informando a la comunidad al instante.

---

## 🛠️ Tecnologías Utilizadas

*   **Backend**: PHP 8.2, Laravel 12.
*   **Frontend Dinámico**: Livewire 3 (componentes reactivos sin JavaScript complejo).
*   **Autenticación**: Laravel Jetstream + Fortify + Socialite (Discord).
*   **Base de Datos**: MySQL / MariaDB (con soporte para SQLite en entornos de prueba).
*   **Estilos**: Tailwind CSS 3 (con paleta de colores personalizada para el gremio).
*   **Permisos**: Spatie Laravel-Permission.
*   **HTTP Client**: GuzzleHttp para consumir APIs externas.
*   **Mapa del Sitio**: Spatie Laravel-Sitemap.
*   **Inteligencia Artificial**: Integración opcional con Ollama (LLaMA3) para asistente interno.
*   **Blog**: API de Blogger v3 (Google) con caché local.

---

## 🧩 Módulos y Funcionalidades

### 1. Landing Page (Pública)
*   **Hero** con llamado a la acción principal.
*   **Ranking de Especialidades Semanal**: Visualización de los líderes de cada oficio con la fama ganada en la semana.
*   **Top 3 Fama PvE y PvP**.
*   **Sección "Nosotros"** con historia del gremio, hitos y video destacado.
*   **Reproductor de Música** (Spotify embed) con el álbum oficial del gremio.
*   **Proceso de Reclutamiento** con requisitos y pasos a seguir.

### 2. Panel de Usuario (Dashboard)
*   Visualización del precio del Oro en tiempo real.
*   Listado y gestión de personajes vinculados a la cuenta.
*   Desvinculación de personajes.
*   Indicadores de membresía en Discord y en el gremio.

### 3. Módulo de Administración
*   **Linhir (Miembros)**: Tabla completa con todos los integrantes del gremio, mostrando su fama, saldo bancario, vinculación a Discord/Web y edición de cumpleaños.
*   **Usuarios**: CRUD completo de usuarios de la plataforma, asignación de roles y permisos.
*   **Roles y Permisos**: Visor de roles existentes y sus permisos asociados.

### 4. Registro de Personaje
*   Buscador de personajes por nombre (vía API).
*   Vinculación segura del personaje al usuario autenticado, verificando si ya está registrado por otro usuario.

### 5. Calculadora de Cultivos
*   Herramienta útil para jugadores que calcula el retorno de inversión (profit) de cultivos según premium, foco, bonificaciones y precio de mercado de semillas y productos.

### 6. Blog (Integración con Blogger)
*   **Publicación desde Blogger**: Administra el contenido desde el panel de Blogger, aprovechando su editor y gestión de imágenes.
*   **Visualización en la Web**: Los artículos se muestran en `/blog` con un diseño responsive, listado con paginación "Cargar más" y detalle completo.
*   **Caché Inteligente**: Las llamadas a la API de Blogger se cachean para reducir la latencia y el consumo de cuota de la API.
*   **SEO Dinámico**: Cada artículo genera metaetiquetas Open Graph y Twitter Cards automáticamente a partir del título, descripción e imagen destacada.
*   **Sitemap Automático**: Las URLs del blog se incluyen en el sitemap semanal para mejorar el posicionamiento en buscadores.

---

## ⏱️ Comandos Programados

El sistema utiliza el programador de tareas de Laravel (Cron) para mantener los datos actualizados automáticamente. A continuación se detallan los comandos principales:

| Comando | Frecuencia | Descripción |
| :--- | :--- | :--- |
| `app:integrantes-de-linhir` | Cada hora | Sincroniza la lista de miembros del gremio y sus estadísticas de por vida (PVE, PVP, Crafteo, Recolección). |
| `app:update-especialidades-semanales` | Cada hora | Calcula la ganancia semanal de cada especialidad para todos los miembros activos. |
| `app:fama_update` | Cada hora (Refuerzos Lunes/Domingo) | Actualiza la fama PvE y PvP semanal. |
| `app:update-gold-price` | Cada hora | Consulta la API externa y guarda el precio actual del Oro. |
| `app:server_check` | Cada 15 segundos (Rango horario) | Verifica el estado del servidor y dispara alertas en Discord. |
| `discord:birthday-notification` | Diario (10:00 AM) | Envía un mensaje de felicitación por cumpleaños al canal de Discord. |
| `sitemap:generate` | Semanal | Genera el archivo `sitemap.xml` para SEO. |
| `blog:check-new-posts` | Cada 30 minutos | Verifica si hay nuevos posts en Blogger y envía notificación a Discord. |

---

## 📁 Estructura del Proyecto

A continuación se resumen las carpetas más relevantes del proyecto:

*   `app/Console/Commands/`: Contiene todos los comandos Artisan personalizados.
*   `app/Livewire/`: Componentes Livewire organizados en módulos (Componentes públicos, Módulos funcionales).
*   `app/Models/`: Modelos Eloquent que representan las tablas de la base de datos.
*   `app/Traits/`: Traits reutilizables para consumir APIs (Albion, AlbionEconomia, Discord, etc.).
*   `database/migrations/`: Migraciones que definen el esquema de la base de datos.
*   `resources/views/`: Vistas Blade (layout, componentes, páginas públicas y privadas).
*   `routes/`: Definición de rutas web, API y consola.

El proyecto sigue el estándar de Laravel, con una clara separación de responsabilidades y un enfoque en la reutilización de código.

---

## 👥 Autor

*   **Kaizerenrique** - *Desarrollador Principal / Fundador* - [GitHub](https://github.com/kaizerenrique)

---

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia **MIT**. Si te resulta útil, ¡no dudes en darle una estrella ⭐ al repositorio o contribuir!

---

> **Linhir** - *"Un gremio de crafters y farmers en Albion Online"*
