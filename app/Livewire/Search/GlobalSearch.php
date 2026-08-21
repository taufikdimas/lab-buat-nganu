<?php

namespace App\Livewire\Search;

use App\Services\SearchService;
use Livewire\Attributes\Url;
use Livewire\Component;

class GlobalSearch extends Component
{
    #[Url(as: 'q')]
    public string $query = '';

    public function render(SearchService $search)
    {
        $results = mb_strlen($this->query) >= 2 ? $search->search(auth()->user(), $this->query) : ['projects' => collect(), 'documents' => collect(), 'users' => collect()];

        return view('livewire.search.global-search', compact('results'));
    }
}
