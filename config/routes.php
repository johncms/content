<?php

declare(strict_types=1);

use Johncms\Content\Controllers\Admin\ContentElementsController;
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
        $route->get('/{type:number}[/[{sectionId:number}[/]]]', [ContentSectionsController::class, 'index'])->setName('content.admin.sections');
        $route->get('/sections/create/{type:number}[/[{sectionId:number}[/]]]', [ContentSectionsController::class, 'create'])->setName('content.admin.sections.create');
        $route->post('/sections/create/{type:number}[/[{sectionId:number}[/]]]', [ContentSectionsController::class, 'create']);
        $route->get('/sections/delete/{type:number}/{id:number}[/]', [ContentSectionsController::class, 'delete'])->setName('content.admin.sections.delete');
        $route->post('/sections/delete/{type:number}/{id:number}[/]', [ContentSectionsController::class, 'delete']);

        // Elements
        $route->get('/{type:number}/{sectionId:number}/{elementId:number}[/]', [ContentSectionsController::class, 'index'])->setName('content.admin.elements');
        $route->get('/elements/create/{type:number}[/[{sectionId:number}[/]]]', [ContentElementsController::class, 'create'])->setName('content.admin.elements.create');
        $route->post('/elements/create/{type:number}[/[{sectionId:number}[/]]]', [ContentElementsController::class, 'create']);
        $route->get('/elements/edit/{elementId:number}[/]', [ContentElementsController::class, 'edit'])->setName('content.admin.elements.edit');
        $route->post('/elements/edit/{elementId:number}[/]', [ContentElementsController::class, 'edit']);


    });
};
