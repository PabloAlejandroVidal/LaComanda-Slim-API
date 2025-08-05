<?php
namespace App\Middlewares;
use App\Interfaces\TokenVerifierInterface;
use App\Repositories\PermisoRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class TokenMiddleware implements MiddlewareInterface
{
    private TokenVerifierInterface $tokenVerifier;
    private array $requiredEmployeeTypes;

    public function __construct(
        TokenVerifierInterface $tokenVerifier,
        array $requiredEmployeeTypes
        )
    {
        $this->tokenVerifier = $tokenVerifier;
        $this->requiredEmployeeTypes = $requiredEmployeeTypes;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $token = $request->getHeaderLine('Bearer');

        try {
            $decodedToken = $this->tokenVerifier->verifyToken($token);
        } catch (\Throwable $e) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $employeeType = $decodedToken->tipo;

        if (!in_array($employeeType, $this->requiredEmployeeTypes)) {
            $response = new SlimResponse();
            $tiposPermitidos = implode(', ', $this->requiredEmployeeTypes);
            $response->getBody()->write(json_encode([
                'status' => 'ERROR',
                'message' => "No tienes permisos suficientes",
                'tipos permitidos' => "[{$tiposPermitidos}]"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $request = $request->withAttribute('decodedToken', $decodedToken);

        return $handler->handle($request);
    }
}



?>