import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Livewire\Shopper\Pages\Product\PendingImports::__invoke
 * @see app/Livewire/Shopper/Pages/Product/PendingImports.php:7
 * @route '/cpanel/products/pending-imports'
 */
const PendingImports = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: PendingImports.url(options),
    method: 'get',
})

PendingImports.definition = {
    methods: ["get","head"],
    url: '/cpanel/products/pending-imports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\Product\PendingImports::__invoke
 * @see app/Livewire/Shopper/Pages/Product/PendingImports.php:7
 * @route '/cpanel/products/pending-imports'
 */
PendingImports.url = (options?: RouteQueryOptions) => {
    return PendingImports.definition.url + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\Product\PendingImports::__invoke
 * @see app/Livewire/Shopper/Pages/Product/PendingImports.php:7
 * @route '/cpanel/products/pending-imports'
 */
PendingImports.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: PendingImports.url(options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\Product\PendingImports::__invoke
 * @see app/Livewire/Shopper/Pages/Product/PendingImports.php:7
 * @route '/cpanel/products/pending-imports'
 */
PendingImports.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: PendingImports.url(options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\Product\PendingImports::__invoke
 * @see app/Livewire/Shopper/Pages/Product/PendingImports.php:7
 * @route '/cpanel/products/pending-imports'
 */
    const PendingImportsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: PendingImports.url(options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\Product\PendingImports::__invoke
 * @see app/Livewire/Shopper/Pages/Product/PendingImports.php:7
 * @route '/cpanel/products/pending-imports'
 */
        PendingImportsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: PendingImports.url(options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\Product\PendingImports::__invoke
 * @see app/Livewire/Shopper/Pages/Product/PendingImports.php:7
 * @route '/cpanel/products/pending-imports'
 */
        PendingImportsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: PendingImports.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    PendingImports.form = PendingImportsForm
export default PendingImports