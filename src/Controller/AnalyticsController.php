<?php

namespace App\Controller;

use App\DTO\LogCountFilterDTO;
use App\Service\LogAnalyticsServiceInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AnalyticsController extends AbstractController
{
    public function __construct(
        private readonly LogAnalyticsServiceInterface $service)
    {
    }

    /**
     * @OA\Get(
     *     path="/count",
     *     summary="Get log count with applied filters",
     *     tags={"Analytics"},
     *     @OA\Parameter(
     *         name="serviceName",
     *         in="query",
     *         description="Filter by service name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         description="Filter by start date (ISO 8601 format)",
     *         required=false,
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         description="Filter by end date (ISO 8601 format)",
     *         required=false,
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Parameter(
     *         name="statusCode",
     *         in="query",
     *         description="Filter by HTTP status code",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched log count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="counter", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameters provided",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error occurred",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/count', name: 'log_count', methods: ['GET'])]
    public function count(Request $request): JsonResponse
    {
        try {
            $filter = LogCountFilterDTO::fromRequest($request);
            $count = $this->service->getCount($filter);

            return $this->json(['counter' => $count]);
        } catch (\InvalidArgumentException $e) {
            // Handle invalid argument error
            return $this->json(['error' => 'Invalid parameters provided.'], JsonResponse::HTTP_BAD_REQUEST);
        } catch (NotFoundHttpException $e) {
            // Handle not found error
            return $this->json(['error' => 'Requested resource not found.'], JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            // General error handler
            return $this->json(['error' => 'An unexpected error occurred.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/delete",
     *     summary="Truncate log entries",
     *     tags={"Analytics"},
     *     @OA\Response(
     *         response=200,
     *         description="Logs truncated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Logs truncated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to truncate logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/delete', name: 'log_count_delete', methods: ['DELETE'])]
    public function truncate(Request $request): JsonResponse
    {
        try {
            // Check authorization token
            $this->checkAuthorizationToken($request);

            // Proceed with truncating the logs if the token is valid
            $this->service->truncateLogs();

            return $this->json(['message' => 'Logs truncated.']);
        } catch (UnauthorizedHttpException $e) {
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            // Handle error during truncation
            return $this->json(['error' => 'Failed to truncate logs.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check if the request contains a valid access token.
     *
     * @param Request $request
     * @throws UnauthorizedHttpException
     */
    private function checkAuthorizationToken(Request $request): void
    {
        // Retrieve the access token from the Authorization header
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            // If the token is missing or invalid
            throw new UnauthorizedHttpException('', 'Authorization token is missing or invalid.');
        }

        $accessToken = $matches[1];

        // Compare the token with the one stored in the environment
        $validAccessToken = $_ENV['ACCESS_TOKEN'];

        if ($accessToken !== $validAccessToken) {
            // If the token doesn't match
            throw new UnauthorizedHttpException('', 'Unauthorized access. Invalid token.');
        }
    }
}
