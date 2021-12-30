local dump = require("dump")
local map = require("map")

script.on_init(function()
    for _, item in pairs(game.item_prototypes) do
        dump.write("item", map.item(item))
        dump.write("recipe", map.rocketLaunchRecipe(item))
    end

    for _, fluid in pairs(game.fluid_prototypes) do
        dump.write("item", map.fluid(fluid))
    end

    for _, entity in pairs(game.entity_prototypes) do
        dump.write("machine", map.machine(entity))
        dump.write("item", map.resource(entity))
        dump.write("recipe", map.miningRecipe(entity))
    end

    game.difficulty_settings.recipe_difficulty = defines.difficulty_settings.recipe_difficulty.normal
    for _, recipe in pairs(game.recipe_prototypes) do
        dump.write("recipe", map.recipe(recipe, "normal"))
    end

    game.difficulty_settings.recipe_difficulty = defines.difficulty_settings.recipe_difficulty.expensive
    for _, recipe in pairs(game.recipe_prototypes) do
        dump.write("recipe", map.recipe(recipe, "expensive"))
    end
end)
