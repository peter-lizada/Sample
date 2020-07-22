public function duplicate($id){
        //get active destination being copied
        $original_destination = HPHotelActiveDestination::find($id);
        $arr_original_destination = $original_destination->toArray();
        $gallery_original_desination = $original_destination->gallery()->get()->toArray();
        
        //get active destination rule being copied
        $original_rule = HPDestinationRule::query()->where('active_destination_id', $id)->first();
        $arr_original_rule = $original_rule->toArray();
        $currency_original_rule = $original_rule->destination_rule_currency()->get()->toArray();

        //change names to add -copy to new destination and rule
        $arr_original_destination['is_active'] = 0;
        if(!empty($arr_original_destination['destination_alias'])){
            $arr_original_destination['destination_alias'] = $arr_original_destination['destination_alias'].'-copy';
        }
        if(!empty($arr_original_destination['name'])){
            $arr_original_destination['name'] = $arr_original_destination['name'].'-copy';
        }
        if(!empty($arr_original_destination['name'])){
            $arr_original_destination['name'] = $arr_original_destination['name'].'-copy';
        }
        $arr_original_destination['unsold_slug'] = $arr_original_destination['unsold_slug'].'-copy';        

        $arr_original_rule['package_name'] = $original_rule['package_name'].'-copy';      

        //create new active destination and gallery image
        $copy_destination = HPHotelActiveDestination::create($arr_original_destination);
        foreach($gallery_original_desination as $images){
            if(strlen($images['image_name']) > 0){
                $copy_destination->gallery()->create($images);
            }
        }

        //create new rule and currency
        $copy_rule = HPDestinationRule::create($arr_original_rule);
        foreach($currency_original_rule as $currency){
            $currency['rule_id'] = $copy_rule->id;
            DestinationRuleCurrency::create($currency);
        }
        //redirect to edit page
        return redirect()->action(
            'ActiveDestinationController@edit', ['id' => $copy_destination->active_destination_id]
        );
        
    }