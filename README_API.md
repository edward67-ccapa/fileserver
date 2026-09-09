# 🚀 API REST Server - Subida de Imágenes y Conversor WebP con Protección de Encriptación (AES-256)

API REST en Laravel configurada con **Middleware de Seguridad Obligatorio (`VerifyEncryptedToken`)**. Rechaza cualquier petición que no contenga un token encriptado válido generado con `APP_KEY`.

---

## 🔒 1. Uso en Postman (Petición Protegida)

Si intentas hacer `POST` sin token en Postman, recibirás un error **`HTTP 403 Forbidden`**:

```json
{
    "success": false,
    "message": "Acceso denegado: Se requiere un token de encriptación válido para realizar esta operación.",
    "error": "missing_encrypted_token"
}
```

### Configuración Correcta en Postman:
* **Método**: `POST`
* **URL**: `http://127.0.0.1:8000/api/upload` (o `http://tu-dominio.com/api/upload`)
* **Headers**:
  - `Accept: application/json`
  - *(Opcional)* `X-Api-Token: <tu_token_encriptado>`

### Body (`form-data`):
| Key | Type | Value (Ejemplo) | Descripción |
| :--- | :--- | :--- | :--- |
| `empresa` | Text | `fombiopol` | Nombre de la empresa |
| `descripcion` | Text | `madicamentos` | Categoría |
| `imagen` | File | `farmaco.png` | Archivo de imagen (Máx. 2MB) |
| `token` | Text | `eyJpdiI6Il...` | **Token de Encriptación Obligatorio (AES-256)** |

---

## 🔑 2. ¿Cómo Generar el Token Encriptado en tu Frontend / CodeIgniter?

En tu proyecto CodeIgniter / PHP Frontend con la librería `CryptoService`:

```php
// Encriptar el permiso CRUD
$payload = array(
    'permission' => 'crud',
    'granted'    => true
);

$token = $this->cryptoservice->encrypt($payload);

// Enviar en el formData de AJAX a la API:
// formData.append("token", token);
```

También puedes generar un token directamente consumiendo el endpoint de la API:
* **`POST /api/crypto/encrypt`**
  ```json
  {
      "data": { "permission": "crud", "granted": true }
  }
  ```

---

## 📥 3. Ejemplo de Respuesta Exitosa (`HTTP 201 Created`)

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
        "url": "http://127.0.0.1:8000/uploads/fombiopol/madicamentos/farmaco.webp",
        "size_kb": 124.5
    }
}
```
