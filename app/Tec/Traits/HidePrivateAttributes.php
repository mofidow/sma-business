<?php

namespace App\Tec\Traits;

use Illuminate\Support\Collection;

trait HidePrivateAttributes
{
    public function hideExtraAttributes($relation_fields)
    {
        if (! empty($relation_fields)) {
            foreach ($relation_fields as $key => $fields) {
                if (! empty($fields)) {
                    logger()->info('$key: ' . $key);
                    if ($key == 'fields') {
                        foreach ($fields as $field) {
                            if (($this->extra_attributes ?? null) && ! $field->show) {
                                $this->extra_attributes->forget($field->name);
                            }
                        }
                    } elseif ($this->relationLoaded($key) && $this->{$key}) {
                        if ($this->$key instanceof Collection) {
                            foreach ($this->$key as &$item) {
                                foreach ($fields as $field) {
                                    if (($item->extra_attributes ?? null) && ! $field->show) {
                                        $item->extra_attributes->forget($field->name);
                                    }
                                }
                            }
                        } else {
                            foreach ($fields as $field) {
                                if (($this->$key->extra_attributes ?? null) && ! $field->show) {
                                    $this->$key->extra_attributes->forget($field->name);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }
}
