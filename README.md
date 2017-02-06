# Image Upload

API based service that allow to save, resize and read images


## Features

- [x] Save on **Filesystem**
- [ ] Save on **AWS**
- [x] Possibility to add Validators
- [x] Possibility to add Filters
- [ ] PHP7 strict mode
- [x] Link with public Postman collection
- [ ] Unit testing
- [ ] Submit on packagist (composer)
- [ ] Host the demo somewhere


## Configuration

Environment variables allowed:
- `SAVE_HANDLER`: (filesystem|aws) where you will save the images
- `AWS_ACCESS_KEY_ID`:
- `AWS_SECRET_ACCESS_KEY`:
- `AWS_REGION`:
- `AWS_BUCKET`:
- `OPTIMIZE`: (0|1) strip exif data in order to reduce image size
- `MAX_DIMENSIONS`: (example: 4096x4096) maximum allowed dimensions
- `MAX_SIZE`: (example: 10240) maximum allowed size in kb
- `ALLOWED_DIMENSIONS`: (example: 1400x460;1280x460;800x600) dimensions allowed when requesting a resize version of a previously uploaded image


## Usage via API

[![Run in Postman](https://run.pstmn.io/button.svg)](https://app.getpostman.com/run-collection/0c7cee4b3b9d99cfbd2f)

In order to receive a JSON response, you should add the following headers:
- `Content-Type: application/json`
- `Accept: application/json; charset=utf-8`

> We are supposing that you uploaded this application on *theappleisonthetable.com*

### GET https://theappleisonthetable.com/

> Return info about a previously uploaded image

Parameters:
- `id`: (*optional*: integer) id of a previously uploaded image

Example GET data: `{}`

Example response:
```
  {
    "ping": "pong"
  }
```

---

Example GET data: `{"id": "5897209a9325f7-78554697"}`

Example response:
```
  {
    "status_code": 200,
    "id": "5897209a9325f7-78554697",
    "path": "http://image-uploader.dev/i/5897209a9325f7-78554697_0_0.jpg",
    "path_local": "data/images/7/9/6/4/5/5/8/7/5897209a9325f7-78554697_0_0.jpg",
    "path_dynamic": "http://image-uploader.dev/i/5897209a9325f7-78554697_#WIDTH#_#HEIGHT#.jpg",
    "width": null,
    "height": null
  }
```

### POST https://theappleisonthetable.com/

> Upload an image

Parameters:
- `source`: (*required*: url|base64 string|uploaded file) you can send a remote url, an image base64 encoded (without new lines in JSON) or upload a file (see an example in the [public/upload.php](../tree/master/public/upload.php)
- `width`: (*optional*: integer) if specified, the original image will be resized to the specified width before uploading it
- `height`: (*optional*: integer) if specified, the original image will be resized to the specified height before uploading it

> Note: if you specify only the `width` or the `height` param, the image will be proportionally resized

Example POST data: `{"source": "...very long base64 string..."}`

Example response:
```
  {
    "status_code": 200,
    "id": "5898c96c4a2d62-52410036",
    "path": "http://image-uploader.dev/i/5898c96c4a2d62-52410036_0_0.jpg",
    "path_local": "data/images/6/3/0/0/1/4/2/5/5898c96c4a2d62-52410036_0_0.jpg",
    "path_dynamic": "http://image-uploader.dev/i/5898c96c4a2d62-52410036_#WIDTH#_#HEIGHT#.jpg",
    "width": null,
    "height": null
  }
```


## Demo

Hosting in progress...


## Examples

If you want to use directly the [Image](../tree/master/src/ImageUploader/Entity/Image.php) object in your application
(and not as a standalone API service), you can find some examples inside the
[public/examples](../tree/master/public/examples) directory.