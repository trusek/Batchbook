<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Formularz usuwania (templates/_delete_form.html.twig): POST z tokenem CSRF.
 * Jedno miejsce na id tokenu i nazwę pola; id jest też na liście bezstanowych tokenów
 * w config/packages/csrf.yaml.
 */
final class DeleteForm
{
    public const TOKEN_ID = 'delete';
    public const TOKEN_FIELD = '_csrf_token';

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokens,
    ) {
    }

    /**
     * @throws AccessDeniedHttpException gdy token jest nieprawidłowy (403)
     */
    public function denyUnlessValid(Request $request): void
    {
        $token = new CsrfToken(self::TOKEN_ID, $request->getPayload()->getString(self::TOKEN_FIELD));

        if (!$this->csrfTokens->isTokenValid($token)) {
            throw new AccessDeniedHttpException('Nieprawidłowy token CSRF.');
        }
    }
}
