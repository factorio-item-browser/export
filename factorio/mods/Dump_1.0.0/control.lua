local dumper = require 'dumper'

script.on_init(function()
    local dump = {}
    for _, item in pairs(game.item_prototypes) do
        if (item.valid) then
            local preparedItem = dumper.prepareItemPrototype(item)
            dump[preparedItem.name] = preparedItem
        end
    end
    dumper.dump('ITEMS', dump)

    dump = {}
    for _, fluid in pairs(game.fluid_prototypes) do
        if (fluid.valid) then
            local preparedItem = dumper.prepareFluidPrototype(fluid)
            dump[preparedItem.name] = preparedItem
        end
    end
    dumper.dump('FLUIDS', dump)


    dump = {}
    for _, machine in pairs(game.entity_prototypes) do
        if (machine.valid and (machine.crafting_categories ~= nil)) then
            -- Only allow the character called "character" and ignore all other pseudo-characters
            if (machine.type ~= 'character' or machine.name == 'character') then
                local preparedMachine = dumper.prepareMachinePrototype(machine)
                dump[machine.name] = preparedMachine
            end
        end
    end
    dumper.dump('MACHINES', dump)


    dump = {}
    game.difficulty_settings.recipe_difficulty = defines.difficulty_settings.recipe_difficulty.normal
    for _, recipe in pairs(game.recipe_prototypes) do
        if (recipe.valid) then
            local preparedRecipe = dumper.prepareRecipePrototype(recipe)
            dump[preparedRecipe.name] = preparedRecipe
        end
    end
    dumper.dump('RECIPES_NORMAL', dump)


    dump = {}
    game.difficulty_settings.recipe_difficulty = defines.difficulty_settings.recipe_difficulty.expensive
    for _, recipe in pairs(game.recipe_prototypes) do
        if (recipe.valid) then
            local preparedRecipe = dumper.prepareRecipePrototype(recipe)
            dump[preparedRecipe.name] = preparedRecipe
        end
    end
    dumper.dump('RECIPES_EXPENSIVE', dump)


    assert(false)
end)
