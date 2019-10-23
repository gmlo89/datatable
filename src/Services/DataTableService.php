<?php

namespace Gmlo\DataTable\Services;


use App\Exports\DataTableExport;
use Maatwebsite\Excel\Facades\Excel;


class DataTable {


    protected $query;
    protected $page = 1;
    protected $rowsPerPage = 25;
    protected $sortBy = null;
    protected $sortDirection = 'asc';
    protected $skip;
    protected $searchables;
    protected $filters;
    protected $custom_columns;

    public function __construct()
    {
        $this->filters = collect();
        $this->custom_columns = collect();
    }

    public function search($limit = 15, $query_input = 'query')
    {
        $words = explode(' ', request()->get($query_input));
        $searchables = isset($this->searchables) ? $this->searchables:[];
        
        foreach ($words as $word) {
            if( empty($word) )
                continue;
            $this->query = $this->query->where(function ($q) use ($word, $searchables) {
                foreach($searchables as $key => $searchable)
                {
                    if( $key == 0 )
                        $q->where($searchable, 'like', "%{$word}%");
                    else
                        $q->orWhere($searchable, 'like', "%{$word}%");
                }
            });
        }

        
        foreach(request()->get('headers') as $header)
        {
            $header = json_decode($header);
            if( isset($header->has_filter) && $header->has_filter == true  && !empty($header->filter) && $filter = $this->filters->where('field', $header->value)->first() )
            {
                $this->query = $filter['callback']($this->query, $header->filter);
            }
        }
    }


    public function configFilter($field, $callback)
    {
        $this->filters->push(compact('field', 'callback'));
        return $this;
    }


    public function configColumn($field, $callback)
    {
        $this->custom_columns->push(compact('field', 'callback'));
        return $this;
    }


    public function query($query)
    {
        if( $pagination = request()->pagination )
        {
            $pagination = json_decode($pagination, true);
            $this->page = isset($pagination['page']) ?$pagination['page']:1;
            $this->rowsPerPage = isset($pagination['rowsPerPage']) ?$pagination['rowsPerPage']:25;
            $this->skip = ($this->page-1) * $this->rowsPerPage;
            $this->sortBy = isset($pagination['sortBy']) ? $pagination['sortBy']:null;
            
            if( isset($pagination['descending']) and $pagination['descending'] )
            {
                $this->sortDirection = 'desc';
            }
        }
        
        $this->query = $query;
        
        return $this;
    }


    public function setFilters()
    {
        $args = func_get_args();
        $this->searchables = $args;
        return $this;
    }

    public function get()
    {
        $this->search();
        
        $query_all = clone $this->query;

        if( !request()->excel and $this->rowsPerPage != -1 )
            $this->query = $this->query->take($this->rowsPerPage)->skip($this->skip);

        if( $this->sortBy )
        {
            $this->query = $this->query->orderBy($this->sortBy, $this->sortDirection);
        }
        
        

        $data = $this->query->get();


        // Cambiar los campos que van a ser personalizados.
        $data = $data->map(function($item){
            foreach($this->custom_columns as $column)
            {
                if( isset($item->{$column['field']}) )
                {
                    $item->{$column['field']} = $column['callback']($item->{$column['field']});
                }
            }
            return $item;
        });
        
        if( request()->excel )
        {
            $export = new DataTableExport($data);
            return Excel::download($export, 'report.xlsx');
        }

        return [
            'items' => $data,
            'total' => $query_all->count()
        ];
    }
}