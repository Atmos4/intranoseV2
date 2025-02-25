<?php
class UploadField extends Field
{
    public static array $FILE_MIME = [
        'image' => [
            'image/webp',
            'image/tiff',
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/x-ms-bmp',
            'image/x-bmp',
            'image/x-portable-bitmap',
            'image/vnd.adobe.photoshop',
            'image/x-eps',
            'application/postscript',
            'application/dicom',
            'application/pcx',
            'application/x-pcx',
            'image/pcx',
            'image/x-pc-paintbrush',
            'image/x-pcx',
            'zz-application/zz-winassoc-pcx',
            'image/jp2',
            'image/heif'
        ],
        'doc' => [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'application/vnd.ms-word.template.macroEnabled.12',
            'application/vnd.oasis.opendocument.text',
        ],
        'pdf' => ['application/pdf'],
        'excel' => [
            'application/vnd.ms-excel',
            'application/x-ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/vnd.ms-excel.template.macroEnabled.12',
            'application/vnd.oasis.opendocument.spreadsheet',
        ],
        'powerpoint' => [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.template',
            'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'application/vnd.oasis.opendocument.presentation',
        ],
        'zip' => ['application/zip'],
    ];
    function set_type(): void
    {
        $this->type = FieldType::File;
    }

    public string $file_type;
    public string $file_name = '';

    public array $allowed_mime = [];

    function __construct(string $key, mixed $value = null, Validator $context = null)
    {
        parent::__construct($key, $value, $context);
        if (isset($_FILES[$this->key])) {
            $this->file_name = basename($_FILES[$this->key]["name"]);
            $this->file_type = $this->get_ext();
        }
    }

    function get_ext()
    {
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    function required(string $msg = null): static
    {
        if ($this->should_test() && !$this->file_name) {
            $this->set_error($msg ?? "Requis");
        }
        return $this;
    }

    function check(string $msg = null): void
    {
        if (isset($_FILES[$this->key])) {
            if ($_FILES[$this->key]["name"] != '') {
                // Check if the error field is ok 
                if (
                    !isset($_FILES[$this->key]['error']) ||
                    is_array($_FILES[$this->key]['error'])
                ) {
                    $this->set_error('Paramètres incorrects');
                } else {
                    // Check $_FILES['upfile']['error'] value.
                    switch ($_FILES[$this->key]['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $this->set_error("Choisissez un fichier");
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                            $this->set_error("Fichier trop lourd");
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $this->set_error("Fichier trop lourd");
                            break;
                        default:
                            $this->set_error("Erreur inconnue");
                    }

                    // Check custom filesize here. 
                    if ($_FILES[$this->key]['size'] > 1000000) {
                        $this->set_error('Fichier trop lourd - ' . round($_FILES[$this->key]['size'] / 1000000, 2) . 'MB');
                    }
                }
            }
        }
    }

    function mime(array $mimes): static
    {
        if ($this->should_test()) {
            $this->allowed_mime = $mimes;
            // Allow certain file formats
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            logger()->debug("finfo", ["info" => $finfo->file($_FILES[$this->key]['tmp_name'])]);
            // Flatten the array
            $flatArray = array_reduce($this->allowed_mime, 'array_merge', []);
            if (
                !in_array(
                    $finfo->file($_FILES[$this->key]['tmp_name']),
                    $flatArray,
                    true
                )
            ) {
                $this->set_error("Seuls les formats " . implode(", ", array_keys($this->allowed_mime)) . " sont acceptés");
            }
        }
        return $this;
    }

    function max_size(int $size): static
    {
        // Check custom filesize here. 
        if ($this->should_test() && $_FILES[$this->key]['size'] > $size) {
            $this->set_error('Fichier trop lourd - ' . round($_FILES[$this->key]['size'] / 1000000, 2) . 'MB');
        }
        return $this;
    }


    function save_file(string $location): string|false
    {
        $path = path($location, $this->file_name);
        mk_dir(dirname($path), true);

        if (file_exists($path)) {
            Toast::error("Le fichier existe déjà");
            return false;
        }

        if (!move_uploaded_file($_FILES[$this->key]["tmp_name"], $path)) {
            Toast::error("Problème à l'enregistrement");
            return false;
        }

        Toast::success("Fichier enregistré");
        return $path;
    }

    function set_file_name(string $name, string $suffix = ""): static
    {
        $this->file_name = $name . ($suffix ?: "." . bin2hex(random_bytes(4)) . "." . $this->get_ext());
        return $this;
    }
}