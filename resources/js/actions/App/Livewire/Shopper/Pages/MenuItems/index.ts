import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
const Index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Index.url(options),
    method: 'get',
})

Index.definition = {
    methods: ["get","head"],
    url: '/cpanel/menu',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
Index.url = (options?: RouteQueryOptions) => {
    return Index.definition.url + queryParams(options)
}

/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
Index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Index.url(options),
    method: 'get',
})
/**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
Index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Index.url(options),
    method: 'head',
})

    /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
    const IndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Index.url(options),
        method: 'get',
    })

            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
        IndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Index.url(options),
            method: 'get',
        })
            /**
* @see \App\Livewire\Shopper\Pages\MenuItems\Index::__invoke
 * @see app/Livewire/Shopper/Pages/MenuItems/Index.php:7
 * @route '/cpanel/menu'
 */
        IndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Index.form = IndexForm
export default Index