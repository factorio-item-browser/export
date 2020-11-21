local dump = require("dump")
local map = require("map")

script.on_init(function()
    for _, item in pairs(game.item_prototypes) do
        if item.valid then
            dump.add("item", map.item(item))
        end
    end

    for _, fluid in pairs(game.fluid_prototypes) do
        if fluid.valid then
            dump.add("fluid", map.fluid(fluid))
        end
    end

    for _, machine in pairs(game.entity_prototypes) do
        if machine.valid and machine.crafting_categories then
            -- Only allow the character called "character" and ignore all other pseudo-characters
            if machine.type ~= "character" or machine.name == "character" then
                dump.add("machine", map.machine(machine))
            end
        end
    end

    game.difficulty_settings.recipe_difficulty = defines.difficulty_settings.recipe_difficulty.normal
    for _, recipe in pairs(game.recipe_prototypes) do
        if recipe.valid then
            dump.add("normal-recipe", map.recipe(recipe))
        end
    end

    game.difficulty_settings.recipe_difficulty = defines.difficulty_settings.recipe_difficulty.expensive
    for _, recipe in pairs(game.recipe_prototypes) do
        if recipe.valid then
            dump.add("expensive-recipe", map.recipe(recipe))
        end
    end

    dump.flush()
end)
