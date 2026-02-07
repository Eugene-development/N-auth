<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Exception;

class NotificationController extends Controller
{
    /**
     * Названия типов услуг на русском
     */
    private const SERVICE_TYPE_LABELS = [
        'consultation' => 'Консультация дизайнера',
        'design-project' => 'Дизайн-проект интерьера',
        'furniture-project' => 'Проект мебели',
        'assembly' => 'Сборка мебели',
        'measurement' => 'Замер помещения',
    ];

    /**
     * Send service request notification email
     */
    public function sendServiceRequestNotification(Request $request)
    {
        try {
            Log::info('Service request notification received', [
                'service_type' => $request->input('service_type'),
                'name' => $request->input('name'),
            ]);

            $request->validate([
                'service_type' => 'required|string|in:consultation,design-project,furniture-project,assembly,measurement',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:50',
                'message' => 'nullable|string|max:2000',
                'source_url' => 'nullable|string|max:500',
            ]);

            $serviceTypeLabel = self::SERVICE_TYPE_LABELS[$request->service_type] ?? $request->service_type;
            
            // Get admin email from env
            $adminEmail = config('app.admin_email', 'admin@novostroy.ru');
            
            // Prepare email content
            $emailData = [
                'service_type' => $serviceTypeLabel,
                'name' => $request->name,
                'phone' => $request->phone,
                'message' => $request->message ?? 'Не указано',
                'source_url' => $request->source_url ?? 'Не указано',
                'submitted_at' => now()->format('d.m.Y H:i:s'),
            ];

            // Send email
            Mail::send('emails.service-request', $emailData, function ($mail) use ($adminEmail, $serviceTypeLabel) {
                $mail->to($adminEmail)
                    ->subject('Новая заявка: ' . $serviceTypeLabel);
            });

            Log::info('Service request notification sent successfully', [
                'service_type' => $request->service_type,
                'to' => $adminEmail,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Уведомление отправлено.',
            ]);

        } catch (ValidationException $e) {
            Log::warning('Service request notification validation failed', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Некорректные данные.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
            
        } catch (Exception $e) {
            Log::error('Service request notification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Не удалось отправить уведомление.',
                'errors' => ['general' => ['Произошла ошибка при отправке.']],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
