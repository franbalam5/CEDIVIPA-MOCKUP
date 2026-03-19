# 🦷 Sistema de Gestión Odontológica y Simulador Clínico

Un sistema web integral diseñado para digitalizar el flujo de trabajo de una clínica odontológica universitaria. Permite la gestión de expedientes médicos, seguimiento de casos mediante tickets, subida de evidencias clínicas y cuenta con un módulo de gamificación (Trivia) para que los estudiantes practiquen diagnósticos con casos reales anonimizados.

## ✨ Características Principales

* **Arquitectura de Roles:** Accesos y vistas personalizadas para **Doctores**, **Pacientes** y **Estudiantes**.
* **Gestión de Casos (Tickets):** Los pacientes pueden abrir reportes detallando síntomas y los doctores pueden dar seguimiento mediante una bitácora interactiva.
* **Hilos de Reincidencia:** Soporte para crear tickets "hijos" vinculados a un caso original si los síntomas persisten.
* **Manejo de Evidencias Multimediales:** Subida múltiple de radiografías, fotografías clínicas o documentos (JPG, PNG, PDF) en reportes y bitácoras, almacenados de forma relacional.
* **Exportación Profesional:** Generación de expedientes médicos completos en formato PDF utilizando Dompdf.
* **Gamificación Dinámica:** Un simulador de diagnósticos que genera exámenes de opción múltiple automáticamente, cruzando la respuesta correcta del profesional con diagnósticos aleatorios de otros casos resueltos.

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8+ (Vanilla)
* **Base de Datos:** MariaDB / MySQL (conexión segura mediante PDO)
* **Frontend:** HTML5, Tailwind CSS (vía CDN), JavaScript (Vanilla)
* **Librerías Externas:** [Dompdf](https://github.com/dompdf/dompdf) (vía Composer)

## 🚀 Instalación y Despliegue Local

Sigue estos pasos para levantar el proyecto en tu entorno local:

### 1. Clonar el repositorio
```bash
git clone [https://github.com/franbalam/nombre-de-tu-repo.git](https://github.com/franbalam/nombre-de-tu-repo.git)
cd nombre-de-tu-repo

2. Configurar la Base de Datos

    Inicia sesión en tu gestor de base de datos local (ej. MariaDB): mysql -u root -p

    Crea una base de datos vacía: CREATE DATABASE odontologia_mockup;

    Importa la estructura y datos de prueba usando el archivo SQL incluido:

Bash

mysql -u root -p odontologia_mockup < odontologia_mockup.sql

3. Instalar Dependencias

Asegúrate de tener Composer instalado en tu sistema para generar la carpeta vendor/ y activar la generación de PDFs:
Bash

composer install

4. Configurar Conexión

Verifica que las credenciales en el archivo config/db.php coincidan con tu configuración local de MariaDB/MySQL (usuario, contraseña y nombre de la base de datos).
5. Iniciar el Servidor

Puedes utilizar el servidor integrado de PHP para pruebas rápidas:
Bash

php -S localhost:8000

Visita http://localhost:8000 en tu navegador.
🔐 Cuentas de Prueba

El sistema incluye las siguientes cuentas preconfiguradas para probar los distintos flujos (la contraseña para todas es 12345):

    Doctor: doctor@test.com

    Paciente: paciente@test.com

    Estudiante: estudiante@test.com

👨‍💻 Autor

Francisco Balam Miranda Becerril

    GitHub: @franbalam5


Para subir esto a tu repositorio, simplemente guárdalo, haz un commit y envíalo:

```bash
git add README.md
git commit -m "docs: agrega README con documentacion del proyecto"
git push origin main
