<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\HomepageBanners;

use App\Enums\HomepageBannerColor;
use App\Enums\HomepageBannerShade;
use App\Support\TailwindTint;
use Filament\Forms\Components\Field;
use Filament\Support\Components\Contracts\HasEmbeddedView;

final class GradientSwatchPicker extends Field implements HasEmbeddedView
{
    public function toEmbeddedHtml(): string
    {
        $id = $this->getId();
        $isDisabled = $this->isDisabled();
        $statePath = $this->getStatePath();
        $selected = TailwindTint::parse($this->getState());

        ob_start(); ?>
        <div
            class="homepage-banner-tints"
            role="group"
            aria-labelledby="<?= e($id) ?>-label"
        >
            <style>
                .homepage-banner-tints { display: flex; flex-direction: column; gap: 0.65rem; }
                .homepage-banner-tints .swatch-row { display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center; }
                .homepage-banner-tints button {
                    cursor: pointer;
                    margin: 0;
                    padding: 0;
                    border: 0;
                    background: transparent;
                }
                .homepage-banner-tints button:disabled { cursor: not-allowed; opacity: 0.55; }
                .homepage-banner-tints .chip {
                    display: grid;
                    place-items: center;
                    width: 1.65rem;
                    height: 1.65rem;
                    border-radius: 9999px;
                    box-shadow: inset 0 0 0 1px rgb(15 23 42 / 0.18);
                }
                .homepage-banner-tints button.is-selected .chip,
                .homepage-banner-tints button:focus-visible .chip {
                    outline: 2px solid #2563eb;
                    outline-offset: 2px;
                }
                .homepage-banner-tints .is-none {
                    background: #fff;
                    color: #94a3b8;
                    font-size: 1.05rem;
                    line-height: 1;
                    border-radius: 0.45rem;
                }
                .homepage-banner-tints .shade-row { display: flex; gap: 0.2rem; width: 100%; }
                .homepage-banner-tints .shade-row button { flex: 1; min-width: 0; }
                .homepage-banner-tints .shade-row .chip {
                    width: 100%;
                    height: 1.35rem;
                    border-radius: 0.3rem;
                }
                .homepage-banner-tints .shade-caption {
                    font-size: 0.7rem;
                    color: #64748b;
                }
            </style>
            <div class="swatch-row">
                <button
                    type="button"
                    title="<?= e(__('backend.banners.gradient_none')) ?>"
                    aria-label="<?= e(__('backend.banners.gradient_none')) ?>"
                    wire:click="$set('<?= e($statePath) ?>', '')"
                    class="<?= $selected === null ? 'is-selected' : '' ?>"
                    <?php if ($isDisabled) { ?> disabled <?php } ?>
                >
                    <span class="chip is-none" aria-hidden="true">&times;</span>
                </button>
                <?php foreach (HomepageBannerColor::cases() as $color) { ?>
                    <?php
                    $preview = TailwindTint::of($color);
                    $token = $selected?->color === $color
                        ? $selected->value()
                        : $preview->value();
                    $isActive = $selected?->color === $color;
                    ?>
                    <button
                        type="button"
                        title="<?= e((string) $color->getLabel()) ?>"
                        aria-label="<?= e((string) $color->getLabel()) ?>"
                        wire:click="$set('<?= e($statePath) ?>', '<?= e($token) ?>')"
                        class="<?= $isActive ? 'is-selected' : '' ?>"
                        <?php if ($isDisabled) { ?> disabled <?php } ?>
                    >
                        <span class="chip" style="background-color: <?= e($preview->oklch()) ?>"></span>
                    </button>
                <?php } ?>
            </div>
            <?php if ($selected instanceof TailwindTint) { ?>
                <div>
                    <div class="shade-caption"><?= e(__('backend.banners.gradient_shade')) ?></div>
                    <div class="shade-row" role="radiogroup" aria-label="<?= e(__('backend.banners.gradient_shade')) ?>">
                        <?php foreach (HomepageBannerShade::cases() as $shade) { ?>
                            <?php $tint = TailwindTint::of($selected->color, $shade); ?>
                            <button
                                type="button"
                                title="<?= e($shade->getLabel()) ?>"
                                aria-label="<?= e($shade->getLabel()) ?>"
                                wire:click="$set('<?= e($statePath) ?>', '<?= e($tint->value()) ?>')"
                                class="<?= $selected->shade === $shade ? 'is-selected' : '' ?>"
                                <?php if ($isDisabled) { ?> disabled <?php } ?>
                            >
                                <span class="chip" style="background-color: <?= e($tint->oklch()) ?>"></span>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php

        return $this->wrapEmbeddedHtml((string) ob_get_clean(), extraWrapperAttributes: [
            'class' => 'fi-fo-toggle-buttons-wrp',
            'tabindex' => '-1',
        ], labelTag: 'div');
    }
}
