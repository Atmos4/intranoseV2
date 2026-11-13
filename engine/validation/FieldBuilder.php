<?php
abstract class FieldBuilder
{
    abstract protected function create(string $key, string $class, ?string $msg);

    public function text(string $key, ?string $msg = null)
    {
        return $this->create($key, StringField::class, $msg);
    }
    public function number(string $key, ?string $msg = null)
    {
        return $this->create($key, NumberField::class, $msg);
    }
    public function textarea(string $key, ?string $msg = null)
    {
        return $this->create($key, TextAreaField::class, $msg);
    }
    public function date(string $key, ?string $msg = null)
    {
        return $this->create($key, DateField::class, $msg);
    }
    public function date_time(string $key, ?string $msg = null)
    {
        return $this->create($key, DateTimeField::class, $msg);
    }
    public function switch(string $key, ?string $msg = null)
    {
        return $this->create($key, SwitchField::class, $msg);
    }
    public function email(string $key, ?string $msg = null)
    {
        return $this->create($key, EmailField::class, $msg);
    }
    public function phone(string $key, ?string $msg = null)
    {
        return $this->create($key, PhoneField::class, $msg);
    }
    public function password(string $key, ?string $msg = null)
    {
        return $this->create($key, PasswordField::class, $msg);
    }
    public function select(string $key)
    {
        return $this->create($key, SelectField::class, null);
    }
    public function url(string $key)
    {
        return $this->create($key, UrlField::class, null);
    }
    public function hidden(string $key)
    {
        return $this->create($key, HiddenField::class, null);
    }
    public function upload(string $key)
    {
        return $this->create($key, UploadField::class, null);
    }
}