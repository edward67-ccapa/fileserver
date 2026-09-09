# 🚀 API REST Server - Subida e Imágenes y Conversor a WebP

API REST en Laravel diseñada para recibir imágenes mediante **Postman (`form-data`)**, validarlas con un tamaño máximo de **2MB**, convertirlas automáticamente a formato **WebP** y ordenarlas en carpetas jerárquicas por empresa y descripción (`/{empresa}/{descripcion}/{imagen}.webp`).

---

## 📌 1. Uso en Postman

### Configuración del Request:
* **Método**: `POST`
* **URL**: `http://tu-dominio.com/api/upload` (o `http://tu-dominio.com/api/v1/images/upload`)
* **Headers**: `Accept: application/json`

### Body (`form-data`):
| Key | Type | Value (Ejemplo) | Descripción |
| :--- | :--- | :--- | :--- |
| `empresa` | Text | `fombiopol` | Nombre de la empresa (se convierte a nombre de carpeta válido). |
| `descripcion` | Text | `madicamentos` | Categoría o descripción (subcarpeta). |
| `imagen` | File | `farmaco.png` | Archivo de imagen (Máx. 2MB, png, jpg, jpeg, webp, gif, etc.). |

---

## 📥 2. Ejemplo de Respuesta JSON (HTTP 201 Created)

```json
{
    "success": true,
    "message": "Imagen procesada y convertida a WebP exitosamente",
    "data": {
        "empresa": "fombiopol",
        "descripcion": "madicamentos",
        "original_name": "farmaco.png",
        "filename": "farmaco.webp",
        "path": "/fombiopol/madicamentos/farmaco.webp",
        "full_path": "/uploads/fombiopol/madicamentos/farmaco.webp",
        "url": "http://tu-dominio.com/uploads/fombiopol/madicamentos/farmaco.webp",
        "size_kb": 124.5
    }
}
```

---

## ⛔ 3. Validaciones y Errores (HTTP 422 Unprocessable Content)

Si no envías los campos obligatorios o el archivo supera los **2MB**:

```json
{
    "success": false,
    "message": "Error de validación en la solicitud.",
    "errors": {
        "imagen": [
            "La imagen sobrepasa el tamaño máximo permitido de 2MB (2048 KB)."
        ]
    }
}
```

---

## 🛠️ 4. Guía de Despliegue en cPanel

1. **Subir archivos al Servidor**:
   - Sube la estructura del proyecto en la raíz de cPanel o en una subcarpeta.
   - Apunta el Document Root del dominio o subdominio a la carpeta `public/`.

2. **Permisos de Archivos**:
   - Asegúrate de que la carpeta `public/uploads/` tenga permisos de escritura (`755` o `775`).
   - Las subcarpetas como `public/uploads/fombiopol/madicamentos/` se crearán automáticamente al subir imágenes.

3. **Acceso Directo a las Imágenes**:
   - Las imágenes convertidas quedan en `public/uploads/empresa/descripcion/archivo.webp`.
   - Cualquier navegador o cliente HTTP puede acceder directamente a la imagen a través de `http://tu-dominio.com/uploads/fombiopol/madicamentos/farmaco.webp`.

4. **Variables de Entorno (`.env`)**:
   - Modifica `APP_URL` en tu archivo `.env` en cPanel con el dominio real de tu aplicación:
     ```env
     APP_URL=https://tu-dominio.com
     ```
