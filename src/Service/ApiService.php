<?php

namespace App\Service;

use stdClass;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception;


class ApiService
{
    const INVALID_REQ = "Invalid request, you need to specify a '%s' property";

    public function form(FormInterface $form, array $content): object
    {
        $form_content = $content;
        if (isset($form_content[AuthService::AUTH_UID])) {
            unset($form_content[AuthService::AUTH_UID]);
        }
        if (!$form->isSubmitted()) {
            $form->submit($form_content);
        }

        $errors = $form->getErrors(true);
        $count = $errors->count();

        $errors_array = [];
        for ($i = 0; $i < $count; ++$i) {
            $error = $errors->offsetGet($i);
            $errors_array[$error->getOrigin()->getName()] = [
                'error' => $error->getMessage()
            ];
        }

        if (!empty($errors_array)) {
            return (object) [
                'valid' => false,
                'response' => new JsonResponse(
                    [
                        'errors' => $errors_array,
                        'code' => 400
                    ],
                    400
                )
            ];
        }

        return (object) [
            'valid' => true,
            'content' => (object) $form_content
        ];
    }

    public function generateError(string $field, string $message, FormInterface $form): void
    {
        $form->get($field)->addError(
            new FormError($message, null, [], null, $field)
        );
    }
}
