import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
const Edit9b4e7a9f025d0afee2ecf43d6990191e = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Edit9b4e7a9f025d0afee2ecf43d6990191e.url(options),
    method: 'get',
})

Edit9b4e7a9f025d0afee2ecf43d6990191e.definition = {
    methods: ["get","head"],
    url: '/cpanel/menu/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
Edit9b4e7a9f025d0afee2ecf43d6990191e.url = (options?: RouteQueryOptions) => {
    return Edit9b4e7a9f025d0afee2ecf43d6990191e.definition.url + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
Edit9b4e7a9f025d0afee2ecf43d6990191e.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Edit9b4e7a9f025d0afee2ecf43d6990191e.url(options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
Edit9b4e7a9f025d0afee2ecf43d6990191e.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Edit9b4e7a9f025d0afee2ecf43d6990191e.url(options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
    const Edit9b4e7a9f025d0afee2ecf43d6990191eForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Edit9b4e7a9f025d0afee2ecf43d6990191e.url(options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
        Edit9b4e7a9f025d0afee2ecf43d6990191eForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Edit9b4e7a9f025d0afee2ecf43d6990191e.url(options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/create'
 */
        Edit9b4e7a9f025d0afee2ecf43d6990191eForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Edit9b4e7a9f025d0afee2ecf43d6990191e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Edit9b4e7a9f025d0afee2ecf43d6990191e.form = Edit9b4e7a9f025d0afee2ecf43d6990191eForm
    /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
const Edit3f65d54ef80a8840e3d4bf3b4995992b = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Edit3f65d54ef80a8840e3d4bf3b4995992b.url(args, options),
    method: 'get',
})

Edit3f65d54ef80a8840e3d4bf3b4995992b.definition = {
    methods: ["get","head"],
    url: '/cpanel/menu/{menuItem}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
Edit3f65d54ef80a8840e3d4bf3b4995992b.url = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return Edit3f65d54ef80a8840e3d4bf3b4995992b.definition.url
            .replace('{menuItem}', parsedArgs.menuItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
Edit3f65d54ef80a8840e3d4bf3b4995992b.get = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Edit3f65d54ef80a8840e3d4bf3b4995992b.url(args, options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
Edit3f65d54ef80a8840e3d4bf3b4995992b.head = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Edit3f65d54ef80a8840e3d4bf3b4995992b.url(args, options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
    const Edit3f65d54ef80a8840e3d4bf3b4995992bForm = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Edit3f65d54ef80a8840e3d4bf3b4995992b.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
        Edit3f65d54ef80a8840e3d4bf3b4995992bForm.get = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Edit3f65d54ef80a8840e3d4bf3b4995992b.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Edit.php:7
 * @route '/cpanel/menu/{menuItem}/edit'
 */
        Edit3f65d54ef80a8840e3d4bf3b4995992bForm.head = (args: { menuItem: string | number } | [menuItem: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Edit3f65d54ef80a8840e3d4bf3b4995992b.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Edit3f65d54ef80a8840e3d4bf3b4995992b.form = Edit3f65d54ef80a8840e3d4bf3b4995992bForm

/**
* Multiple routes resolve to \App\Livewire\Shopper\Pages\MenuItems\Edit::Edit, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `Edit['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const Edit = {
    '/cpanel/menu/create': Edit9b4e7a9f025d0afee2ecf43d6990191e,
    '/cpanel/menu/{menuItem}/edit': Edit3f65d54ef80a8840e3d4bf3b4995992b,
}

export default Edit