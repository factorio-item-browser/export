local map = {}

--- Maps a fluid prototype.
-- @param prototype table: The fluid prototype to map.
-- @return table: The mapped data.
function map.fluid(prototype)
    return {
        name = prototype.name,
        localised_name = prototype.localised_name,
        localised_description = prototype.localised_description,
    }
end

--- Maps an item prototype.
-- @param prototype table: The item prototype to map.
-- @return table: The mapped data.
function map.item(prototype)
    local item = {
        name = prototype.name,
        localised_name = prototype.localised_name,
        localised_description = prototype.localised_description,
        localised_entity_name = nil,
        localised_entity_description = nil,
    }
    if prototype.place_result then
        item.localised_entity_name = prototype.place_result.localised_name
        item.localised_entity_description = prototype.place_result.localised_description
    end

    return item
end

--- Maps a machine prototype.
-- @param prototype table: The machine prototype to map.
-- @return table: The mapped data.
function map.machine(prototype)
    local machine = {
        name = prototype.name,
        localised_name = prototype.localised_name,
        localised_description = prototype.localised_description,
        crafting_categories = {},
        crafting_speed = prototype.crafting_speed,
        item_slots = 0,
        fluid_input_slots = 0,
        fluid_output_slots = 0,
        module_slots = prototype.module_inventory_size,
        energy_usage = (prototype.energy_usage or 0) * 60,
    }

    for category, flag in pairs(prototype.crafting_categories) do
        if flag then
            table.insert(machine.crafting_categories, category)
        end
    end

    if prototype.type == "furnace" then
        -- Furnaces are forced to have exactly one ingredient slot, but it is not set in ingredient_count.
        machine.item_slots = 1
    elseif not prototype.ingredient_count then
        -- Character entities do not specify an ingredient_count, but actually have unlimited ones.
        machine.item_slots = 255
    else
        machine.item_slots = machine.ingredient_count
    end

    for _, fluidbox in pairs(prototype.fluidbox_prototypes) do
        if fluidbox.production_type == "input" then
            machine.fluid_input_slots = machine.fluid_input_slots + 1
        elseif fluidbox.production_type == "output" then
            machine.fluid_output_slots = machine.fluid_output_slots + 1
        end
    end

    return machine
end

--- Maps a recipe prototype.
-- @param prototype table: The recipe prototype to map.
-- @return table: The mapped data.
function map.recipe(prototype)
    local recipe = {
        name = prototype.name,
        localised_name = prototype.localised_name,
        localised_description = prototype.localised_description,
        crafting_time = prototype.energy,
        crafting_category = prototype.category,
        ingredients = {},
        products = {},
    }

    for _, ingredient in pairs(prototype.ingredients) do
        table.insert(recipe.ingredients, map.ingredient(ingredient))
    end

    for _, product in pairs(prototype.products) do
        table.insert(recipe.products, map.product(product))
    end

    return recipe
end

--- Maps an ingredient of a recipe.
-- @param prototype table: The ingredient to map.
-- @return table: The mapped data.
function map.ingredient(prototype)
    return {
        type = prototype.type,
        name = prototype.name,
        amount = prototype.amount,
    }
end

--- Maps an product of a recipe.
-- @param prototype table: The product to map.
-- @return table: The mapped data.
function map.product(prototype)
    local product = {
        type = prototype.type,
        name = prototype.name,
        amount_min = prototype.amount_min or 1.,
        amount_max = prototype.amount_max or 1.,
        probability = prototype.probability or 1.,
    }

    if prototype.amount then
        -- amount is a simpler way of defining all three of the amount values.
        product.amount_min = prototype.amount
        product.amount_max = prototype.amount
        product.probability = 1.
    end

    return product
end

--- Maps an icon from the prototype.
-- @param prototype table: The prototype to map.
-- @return table or nil: The mapped data, if there was anything to map.
function map.icon(prototype)
    local icon = {
        type = prototype.type,
        name = prototype.name,
        layers = {},
        size = prototype.icon_size,
    }

    if prototype.icons then
        -- "icons" are the layers overlaying each other to form the final icon.

        -- If the first icon has an icon_size set, it actually overwrites the top-level one.
        if prototype.icons[1].icon_size then
            icon.size = prototype.icons[1].icon_size
        end

        for _, layer in pairs(prototype.icons) do
            table.insert(icon.layers, map.layer(layer, icon.size))
        end
    elseif prototype.icon then
        -- When "icon" is specified, then it represents the only, not-manipulated layer of the final icon.
        table.insert(icon.layers, map.layer({
            icon = prototype.icon,
        }))
    else
        -- We do not actually have any icon defined, so throw away all the data.
        return nil
    end

    return icon
end

--- Maps an icon layer from the prototype.
-- @param prototype table: The prototype to map.
-- @param size int: The size of the icon for relative scaling.
-- @return table: The mapped data.
-- @todo There is some strange correlation going on between the first layer and all other ones which needs to get addressed while dumping.
function map.layer(prototype, size)
    local scale
    if prototype.icon_size then
        scale = size / prototype.icon_size * (prototype.scale or 1.)
    else
        scale = prototype.icon_size
    end

    local layer = {
        file = prototype.icon,
        shift_x = nil,
        shift_y = nil,
        scale = scale,
        tint_red = nil,
        tint_green = nil,
        tint_blue = nil,
        tint_alpha = nil,
    }

    if prototype.shift then
        layer.shift_x = prototype.shift[1]
        layer.shift_y = prototype.shift[2]
    end

    if prototype.tint then
        layer.tint_red = prototype.tint.r
        layer.tint_green = prototype.tint.g
        layer.tint_blue = prototype.tint.b
        layer.tint_alpha = prototype.tint.a
    end

    return layer
end

return map
