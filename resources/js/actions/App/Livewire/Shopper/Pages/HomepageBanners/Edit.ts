import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/create'
 */
const Edit1f370c87d0c2605cc933d22396779212 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Edit1f370c87d0c2605cc933d22396779212.url(options),
    method: 'get',
})

Edit1f370c87d0c2605cc933d22396779212.definition = {
    methods: ["get","head"],
    url: '/cpanel/banners/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/create'
 */
Edit1f370c87d0c2605cc933d22396779212.url = (options?: RouteQueryOptions) => {
    return Edit1f370c87d0c2605cc933d22396779212.definition.url + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/create'
 */
Edit1f370c87d0c2605cc933d22396779212.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Edit1f370c87d0c2605cc933d22396779212.url(options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/create'
 */
Edit1f370c87d0c2605cc933d22396779212.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Edit1f370c87d0c2605cc933d22396779212.url(options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/create'
 */
    const Edit1f370c87d0c2605cc933d22396779212Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Edit1f370c87d0c2605cc933d22396779212.url(options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/create'
 */
        Edit1f370c87d0c2605cc933d22396779212Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Edit1f370c87d0c2605cc933d22396779212.url(options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/create'
 */
        Edit1f370c87d0c2605cc933d22396779212Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Edit1f370c87d0c2605cc933d22396779212.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Edit1f370c87d0c2605cc933d22396779212.form = Edit1f370c87d0c2605cc933d22396779212Form
    /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/{banner}/edit'
 */
const Edit34d56b2dcc6381e5211f298be75dda81 = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Edit34d56b2dcc6381e5211f298be75dda81.url(args, options),
    method: 'get',
})

Edit34d56b2dcc6381e5211f298be75dda81.definition = {
    methods: ["get","head"],
    url: '/cpanel/banners/{banner}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/{banner}/edit'
 */
Edit34d56b2dcc6381e5211f298be75dda81.url = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { banner: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    banner: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        banner: args.banner,
                }

    return Edit34d56b2dcc6381e5211f298be75dda81.definition.url
            .replace('{banner}', parsedArgs.banner.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/{banner}/edit'
 */
Edit34d56b2dcc6381e5211f298be75dda81.get = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Edit34d56b2dcc6381e5211f298be75dda81.url(args, options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/{banner}/edit'
 */
Edit34d56b2dcc6381e5211f298be75dda81.head = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Edit34d56b2dcc6381e5211f298be75dda81.url(args, options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/{banner}/edit'
 */
    const Edit34d56b2dcc6381e5211f298be75dda81Form = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Edit34d56b2dcc6381e5211f298be75dda81.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/{banner}/edit'
 */
        Edit34d56b2dcc6381e5211f298be75dda81Form.get = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Edit34d56b2dcc6381e5211f298be75dda81.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/banners/{banner}/edit'
 */
        Edit34d56b2dcc6381e5211f298be75dda81Form.head = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Edit34d56b2dcc6381e5211f298be75dda81.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Edit34d56b2dcc6381e5211f298be75dda81.form = Edit34d56b2dcc6381e5211f298be75dda81Form

/**
* Multiple routes resolve to \App\Livewire\Shopper\Pages\HomepageBanners\Edit::Edit, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `Edit['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const Edit = {
    '/cpanel/banners/create': Edit1f370c87d0c2605cc933d22396779212,
    '/cpanel/banners/{banner}/edit': Edit34d56b2dcc6381e5211f298be75dda81,
}

export default Edit