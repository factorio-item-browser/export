local dump = require("dump")
local map = require("map")

script.on_init(function()
    local dump_data = {
        items = {},
        fluids = {},
        machines = {},
        normal_recipes = {},
        expensive_recipes = {},
    }

    for _, item in pairs(game.item_prototypes) do
        if item.valid then
            table.insert(dump_data.items, map.item(item))
        end
    end

    for _, fluid in pairs(game.fluid_prototypes) do
        if fluid.valid then
            table.insert(dump_data.fluids, map.fluid(fluid))
        end
    end

    for _, machine in pairs(game.entity_prototypes) do
        if machine.valid and machine.crafting_categories then
            -- Only allow the character called "character" and ignore all other pseudo-characters
            if machine.type ~= "character" or machine.name == "character" then
                table.insert(dump_data.machines, map.machine(machine))
            end
        end
    end

    game.difficulty_settings.recipe_difficulty = defines.difficulty_settings.recipe_difficulty.normal
    for _, recipe in pairs(game.recipe_prototypes) do
        if recipe.valid then
            table.insert(dump_data.normal_recipes, map.recipe(recipe))
        end
    end

    game.difficulty_settings.recipe_difficulty = defines.difficulty_settings.recipe_difficulty.expensive
    for _, recipe in pairs(game.recipe_prototypes) do
        if recipe.valid then
            table.insert(dump_data.expensive_recipes, map.recipe(recipe))
        end
    end

    dump.write("CONTROL", dump_data)
end)
