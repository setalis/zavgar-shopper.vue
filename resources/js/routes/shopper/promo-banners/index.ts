import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Index::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Index.php:7
 * @route '/cpanel/promo-banners'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/cpanel/promo-banners',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Index::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Index.php:7
 * @route '/cpanel/promo-banners'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Index::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Index.php:7
 * @route '/cpanel/promo-banners'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Index::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Index.php:7
 * @route '/cpanel/promo-banners'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Index::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Index.php:7
 * @route '/cpanel/promo-banners'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Index::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Index.php:7
 * @route '/cpanel/promo-banners'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Index::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Index.php:7
 * @route '/cpanel/promo-banners'
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
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/cpanel/promo-banners/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/create'
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
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/{banner}/edit'
 */
export const edit = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/cpanel/promo-banners/{banner}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/{banner}/edit'
 */
edit.url = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{banner}', parsedArgs.banner.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/{banner}/edit'
 */
edit.get = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/{banner}/edit'
 */
edit.head = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/{banner}/edit'
 */
    const editForm = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/{banner}/edit'
 */
        editForm.get = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\HomepageBanners\Edit::__invoke
 * @see app/Livewire/Shopper/Pages/HomepageBanners/Edit.php:7
 * @route '/cpanel/promo-banners/{banner}/edit'
 */
        editForm.head = (args: { banner: string | number } | [banner: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
const promoBanners = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
edit: Object.assign(edit, edit),
}

export default promoBanners