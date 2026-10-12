<?php

namespace App\Livewire\Modulo\Ctas;

use Livewire\Component;
use App\Models\Cta;

class CtaShow extends Component
{
    public int $ctaId;
    public ?Cta $cta = null;

    public function mount(int $id): void
    {
        $this->ctaId = $id;
        $this->load();
    }

    public function load(): void
    {
        $this->cta = Cta::with([
            'composition.weapons',
            'participants.weapon',
            'participants.personaje',
        ])->find($this->ctaId);
    }

    public function render()
    {
        return view('livewire.modulo.ctas.cta-show', [
            'cta' => $this->cta,
            'ocupacion' => $this->cta?->weaponOccupancy() ?? [],
        ]);
    }
}
