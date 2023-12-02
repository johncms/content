<?php

declare(strict_types=1);

use Johncms\Content\Controllers\Admin\ContentSectionsController;
use Johncms\Content\Controllers\Admin\ContentTypesController;
use League\Route\RouteGroup;
use League\Route\Router;

return function (Router $router) {
    $router->group('/admin/content', function (RouteGroup $route) {
        // Types
        $route->get('/', [ContentTypesController::class, 'index'])->setName('content.admin.index');
        $route->get('/types/create[/]', [ContentTypesController::class, 'create'])->setName('content.admin.createContentType');
        $route->post('/types/create[/]', [ContentTypesController::class, 'create']);
        $route->get('/types/delete/{id:number}[/]', [ContentTypesController::class, 'delete'])->setName('content.admin.delete');
        $route->post('/types/delete/{id:number}[/]', [ContentTypesController::class, 'delete']);

        // Sections
        $route->get('/{type:number}[/]', [ContentSectionsController::class, 'index'])->setName('content.admin.sections');
        $route->get('/{type:number}/sections/create[/]', [ContentSectionsController::class, 'create'])->setName('content.admin.sections.create');
        $route->post('/{type:number}/sections/create[/]', [ContentSectionsController::class, 'create']);
        $route->get('/{type:number}/sections/delete/{id:number}[/]', [ContentSectionsController::class, 'delete'])->setName('content.admin.sections.delete');
        $route->post('/{type:number}/sections/delete/{id:number}[/]', [ContentSectionsController::class, 'delete']);
    });
};
