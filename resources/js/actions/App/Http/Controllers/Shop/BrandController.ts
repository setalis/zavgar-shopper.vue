import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\BrandController::index
 * @see app/Http/Controllers/Shop/BrandController.php:19
 * @route '/brands'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/brands',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\BrandController::index
 * @see app/Http/Controllers/Shop/BrandController.php:19
 * @route '/brands'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\BrandController::index
 * @see app/Http/Controllers/Shop/BrandController.php:19
 * @route '/brands'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\BrandController::index
 * @see app/Http/Controllers/Shop/BrandController.php:19
 * @route '/brands'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\BrandController::index
 * @see app/Http/Controllers/Shop/BrandController.php:19
 * @route '/brands'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\BrandController::index
 * @see app/Http/Controllers/Shop/BrandController.php:19
 * @route '/brands'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\BrandController::index
 * @see app/Http/Controllers/Shop/BrandController.php:19
 * @route '/brands'
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
* @see \App\Http\Controllers\Shop\BrandController::show
 * @see app/Http/Controllers/Shop/BrandController.php:66
 * @route '/brands/{brand}'
 */
export const show = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/brands/{brand}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\BrandController::show
 * @see app/Http/Controllers/Shop/BrandController.php:66
 * @route '/brands/{brand}'
 */
show.url = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { brand: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
            args = { brand: args.slug }
        }
    
    if (Array.isArray(args)) {
        args = {
                    brand: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        brand: typeof args.brand === 'object'
                ? args.brand.slug
                : args.brand,
                }

    return show.definition.url
            .replace('{brand}', parsedArgs.brand.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\BrandController::show
 * @see app/Http/Controllers/Shop/BrandController.php:66
 * @route '/brands/{brand}'
 */
show.get = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\BrandController::show
 * @see app/Http/Controllers/Shop/BrandController.php:66
 * @route '/brands/{brand}'
 */
show.head = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\BrandController::show
 * @see app/Http/Controllers/Shop/BrandController.php:66
 * @route '/brands/{brand}'
 */
    const showForm = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\BrandController::show
 * @see app/Http/Controllers/Shop/BrandController.php:66
 * @route '/brands/{brand}'
 */
        showForm.get = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\BrandController::show
 * @see app/Http/Controllers/Shop/BrandController.php:66
 * @route '/brands/{brand}'
 */
        showForm.head = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const BrandController = { index, show }

export default BrandController