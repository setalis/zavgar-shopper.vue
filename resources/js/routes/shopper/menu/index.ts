import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/cpanel/menu',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/cpanel/menu/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
export const edit = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/cpanel/menu/{menuItem}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
edit.url = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { menuItem: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    menuItem: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        menuItem: args.menuItem,
                }

    return edit.definition.url
            .replace('{menuItem}', parsedArgs.menuItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
edit.get = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
edit.head = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
    const editForm = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
        editForm.get = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
        editForm.head = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
const menu = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
edit: Object.assign(edit, edit),
}

export default menu