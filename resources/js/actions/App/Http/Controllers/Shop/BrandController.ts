import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\BrandController::__invoke
 * @see app/Http/Controllers/Shop/BrandController.php:15
 * @route '/brands/{brand}'
 */
const BrandController = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: BrandController.url(args, options),
    method: 'get',
})

BrandController.definition = {
    methods: ["get","head"],
    url: '/brands/{brand}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\BrandController::__invoke
 * @see app/Http/Controllers/Shop/BrandController.php:15
 * @route '/brands/{brand}'
 */
BrandController.url = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
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

    return BrandController.definition.url
            .replace('{brand}', parsedArgs.brand.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\BrandController::__invoke
 * @see app/Http/Controllers/Shop/BrandController.php:15
 * @route '/brands/{brand}'
 */
BrandController.get = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: BrandController.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\BrandController::__invoke
 * @see app/Http/Controllers/Shop/BrandController.php:15
 * @route '/brands/{brand}'
 */
BrandController.head = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: BrandController.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\BrandController::__invoke
 * @see app/Http/Controllers/Shop/BrandController.php:15
 * @route '/brands/{brand}'
 */
    const BrandControllerForm = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: BrandController.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\BrandController::__invoke
 * @see app/Http/Controllers/Shop/BrandController.php:15
 * @route '/brands/{brand}'
 */
        BrandControllerForm.get = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: BrandController.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\BrandController::__invoke
 * @see app/Http/Controllers/Shop/BrandController.php:15
 * @route '/brands/{brand}'
 */
        BrandControllerForm.head = (args: { brand: string | { slug: string } } | [brand: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: BrandController.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    BrandController.form = BrandControllerForm
export default BrandController