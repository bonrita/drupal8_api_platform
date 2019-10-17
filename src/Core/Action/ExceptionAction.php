<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Action;

use Drupal\api_platform\Core\Util\ErrorFormatGuesser;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Renders a normalized exception for a given {@see \Symfony\Component\Debug\Exception\FlattenException}.
 *
 * Usage:
 *
 *     $exceptionAction = new ExceptionAction(
 *         new Serializer(),
 *         [
 *             'jsonproblem' => ['application/problem+json'],
 *             'jsonld' => ['application/ld+json'],
 *         ],
 *         [
 *             ExceptionInterface::class => Response::HTTP_BAD_REQUEST,
 *             InvalidArgumentException::class => Response::HTTP_BAD_REQUEST,
 *         ]
 *     );
 */
final class ExceptionAction
{
    private $serializer;
    private $errorFormats;
    private $exceptionToStatus;

    /**
     * @param array $errorFormats      A list of enabled formats, the first one will be the default
     * @param array $exceptionToStatus A list of exceptions mapped to their HTTP status code
     */
    public function __construct(SerializerInterface $serializer, array $errorFormats, array $exceptionToStatus = [])
    {
        $this->serializer = $serializer;
        $this->errorFormats = $errorFormats;
        $this->exceptionToStatus = $exceptionToStatus;
    }

    /**
     * Converts a an exception to a JSON response.
     */
    public function __invoke(FlattenException $exception, Request $request): Response
    {
        $exceptionClass = $exception->getClass();
        $statusCode = $exception->getStatusCode();

        foreach ($this->exceptionToStatus as $class => $status) {
            if (is_a($exceptionClass, $class, true)) {
                $statusCode = $status;

                break;
            }
        }

        $headers = $exception->getHeaders();
        $format = ErrorFormatGuesser::guessErrorFormat($request, $this->errorFormats);
        $headers['Content-Type'] = sprintf('%s; charset=utf-8', $format['value'][0]);
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'deny';

        return new Response($this->serializer->serialize($exception, $format['key'], ['statusCode' => $statusCode]), $statusCode, $headers);
    }
}
