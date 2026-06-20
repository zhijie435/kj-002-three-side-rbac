<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class BusinessException extends RuntimeException
{
    protected int $errorCode;

    public function __construct(
        string $message = '',
        int $errorCode = 400,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, $previous);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public static function make(string $message, int $code = 400): self
    {
        return new self($message, $code);
    }

    public static function notFound(string $message = '资源不存在'): self
    {
        return new self($message, 404);
    }

    public static function forbidden(string $message = '没有权限执行此操作'): self
    {
        return new self($message, 403);
    }

    public static function validationFailed(string $message = '参数验证失败'): self
    {
        return new self($message, 422);
    }

    public static function unauthorized(string $message = '未授权，请先登录'): self
    {
        return new self($message, 401);
    }
}
