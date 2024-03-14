<?php

namespace App\Livewire;

use App\Models\Stop;
use Livewire\Component;

class ShowStop extends Component
{
    public Stop $stop;

    public function mount()
    {
        $this->stop = $this->stop->load('routes.companies');
    }

    public function render()
    {
        return view('livewire.show-stop');
    }
}
