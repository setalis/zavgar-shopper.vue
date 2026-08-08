import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\LocaleController::update
 * @see app/Http/Controllers/LocaleController.php:12
 * @route '/locale'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/locale',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\LocaleController::update
 * @see app/Http/Controllers/LocaleController.php:12
 * @route '/locale'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LocaleController::update
 * @see app/Http/Controllers/LocaleController.php:12
 * @route '/locale'
 */
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\LocaleController::update
 * @see app/Http/Controllers/LocaleController.php:12
 * @route '/locale'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\LocaleController::update
 * @see app/Http/Controllers/LocaleController.php:12
 * @route '/locale'
 */
        updateForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const locale = {
    update: Object.assign(update, update),
}

export default locale