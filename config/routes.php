<?php

declare(strict_types=1);

use Johncms\Content\Controllers\Admin\ContentElementsController;
use Johncms\Content\Controllers\Admin\ContentSectionsController;
use Johncms\Content\Controllers\Admin\ContentTypesController;
use Johncms\Content\Controllers\Public\PublicContentController;
use Johncms\Router\RouteCollection;

return function (RouteCollection $router) {
    $router->group('/admin/content', function (RouteCollection $route) {
        // Types
        $route->get('/', [ContentTypesController::class, 'index'])->setName('content.admin.index');
        $route->map(['GET', 'POST'], '/types/create/', [ContentTypesController::class, 'create'])->setName('content.admin.type.create');
        $route->map(['GET', 'POST'], '/types/delete/{id:number}/', [ContentTypesController::class, 'delete'])->setName('content.admin.type.delete');
        $route->map(['GET', 'POST'], '/types/edit/{id:number}/', [ContentTypesController::class, 'edit'])->setName('content.admin.type.edit');

        // Sections
        $route->get('/{type:number}/{sectionId:number?}', [ContentSectionsController::class, 'index'])->setName('content.admin.sections');
        $route->map(['GET', 'POST'], '/sections/create/{type:number}/{sectionId:number?}', [ContentSectionsController::class, 'create'])->setName('content.admin.sections.create');
        $route->map(['GET', 'POST'], '/sections/delete/{type:number}/{id:number}/', [ContentSectionsController::class, 'delete'])->setName('content.admin.sections.delete');
        $route->map(['GET', 'POST'], '/sections/edit/{id:number}/', [ContentSectionsController::class, 'edit'])->setName('content.admin.sections.edit');

        // Elements
        $route->get('/{type:number}/{sectionId:number}/{elementId:number}[/]', [ContentSectionsController::class, 'index'])->setName('content.admin.elements');
        $route->map(['GET', 'POST'], '/elements/create/{type:number}/{sectionId:number?}', [ContentElementsController::class, 'create'])->setName('content.admin.elements.create');
        $route->map(['GET', 'POST'], '/elements/edit/{elementId:number}/', [ContentElementsController::class, 'edit'])->setName('content.admin.elements.edit');
        $route->map(['GET', 'POST'], '/elements/delete/{id:number}/', [ContentElementsController::class, 'delete'])->setName('content.admin.elements.delete');
    });

    // Public
    $router->get('/{type:slug}', [PublicContentController::class, 'index'])->setName('content.type')->setPriority(-100000);
    $router->get('/{type:slug}/{section:path}/{element:slug}.html', [PublicContentController::class, 'element'])->setName('content.element')->setPriority(-100010);
    $router->get('/{type:slug}/{section:path}', [PublicContentController::class, 'section'])->setName('content.section')->setPriority(-100020);
};
