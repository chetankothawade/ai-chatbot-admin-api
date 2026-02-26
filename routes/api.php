<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\{
    AuthController,
    UserController,
    ModuleController,
    UserPermissionController,
    DashboardController,
    EditorUploadController,
    RoleModuleController,
    ActivityLogController
};

use App\Http\Controllers\Api\Chat\{
    ChatSessionController,
    MessageController,
    MetadataController,
    UsageController,
    SearchController,
    ParticipantController
};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are automatically prefixed with /api.
| Sanctum is used for authentication.
|
*/

/*
|--------------------------------------------------------------------------
| Admin Authentication (Public)
|--------------------------------------------------------------------------
*/

Route::middleware(['ip.throttle', 'burst.throttle'])->group(function () {
    Route::post('/register',        [AuthController::class, 'register']);
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
    Route::post('/refresh',         [AuthController::class, 'refreshToken']);


    /*
|--------------------------------------------------------------------------
| Protected Routes (Requires auth:sanctum)
|--------------------------------------------------------------------------
*/
    Route::middleware(['auth:sanctum', 'role.throttle', 'token.throttle'])->group(function () {

        /*
    |--------------------------------------------------------------------------
    | Authenticated Admin User
    |--------------------------------------------------------------------------
    */
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);


        /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    | All user management routes.
    */
        Route::prefix('users')->group(function () {

            Route::get('/getList',       [UserController::class, 'getUserList']);
            Route::get('/',              [UserController::class, 'index']);
            Route::post('/',             [UserController::class, 'store']);
            Route::get('/{uuid}',        [UserController::class, 'show']);
            Route::get('/{uuid}/edit',   [UserController::class, 'edit']);
            Route::put('/{uuid}',        [UserController::class, 'update']);
            Route::delete('/{uuid}',     [UserController::class, 'destroy']);
            Route::put('/{uuid}/active', [UserController::class, 'active']);
        });


        /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    */
        Route::prefix('modules')->group(function () {
            // Parent/Module list
            Route::get('/module/getList', [ModuleController::class, 'getModuleList']);

            // CRUD
            Route::get('/',            [ModuleController::class, 'index']);
            Route::post('/',           [ModuleController::class, 'store']);
            Route::get('/{uuid}',      [ModuleController::class, 'show']);
            Route::get('/{uuid}/edit', [ModuleController::class, 'edit']);
            Route::put('/{uuid}',      [ModuleController::class, 'update']);
            Route::delete('/{uuid}',   [ModuleController::class, 'destroy']);
            Route::put('/{uuid}/active', [ModuleController::class, 'active']);
        });

        /*
    |--------------------------------------------------------------------------
    | Role-Modules
    |--------------------------------------------------------------------------
    */
        Route::prefix('role-modules')->group(function () {
            Route::get('/matrix', [RoleModuleController::class, 'matrix']);
            Route::post('/toggle', [RoleModuleController::class, 'toggle']);
        });


        /*
    |--------------------------------------------------------------------------
    | User Permissions
    |--------------------------------------------------------------------------
    */
        Route::prefix('user-permissions')->group(function () {
            Route::get('/side-menu', [UserPermissionController::class, 'sidebarMenu']);
            Route::post('/toggle',  [UserPermissionController::class, 'toggle']);
            Route::get('/{uuid}/getAll',   [UserPermissionController::class, 'getUsersModulesPermission']);
            Route::get('/{uuid}/module-access', [UserPermissionController::class, 'userModuleAccess']);
        });

        /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

        /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);

        /*
    |--------------------------------------------------------------------------
    | Chat
    |--------------------------------------------------------------------------
    */
        Route::prefix('chat')->group(function () {

            // Sessions
            Route::delete('sessions/{id}/messages', [ChatSessionController::class, 'clearMessages']);
            Route::put('sessions/{id}/context', [ChatSessionController::class, 'updateContext']);
            Route::put('sessions/{id}/model', [ChatSessionController::class, 'updateModel']);
            Route::patch('sessions/{id}/pin', [ChatSessionController::class, 'togglePin']);
            Route::apiResource('sessions', ChatSessionController::class);
         
            // Messages
            Route::post('messages/send', [MessageController::class, 'send']);
            Route::get('messages', [MessageController::class, 'index']);
            Route::post('messages/{id}/regenerate', [MessageController::class, 'regenerate']);
            Route::delete('messages/{id}', [MessageController::class, 'delete']);

            // Streaming
            Route::post('messages/stream', [MessageController::class, 'stream']);

            // Metadata
            Route::post('messages/{id}/metadata', [MetadataController::class, 'store']);
            Route::get('messages/{id}/metadata', [MetadataController::class, 'index']);

            // Usage
            Route::get('usage', [UsageController::class, 'index']);
            Route::get('usage/{chat_id}', [UsageController::class, 'chatUsage']);

            // Search
            Route::get('search', [SearchController::class, 'search']);

            // Participants
            Route::post('sessions/{id}/participants', [ParticipantController::class, 'add']);
            Route::delete('sessions/{id}/participants/{user_id}', [ParticipantController::class, 'remove']);
        });

        /*
    |--------------------------------------------------------------------------
    | Editor Uploads
    |--------------------------------------------------------------------------
    */
        Route::post(
            '/editor/upload',
            [EditorUploadController::class, 'upload']
        );
    });
});
