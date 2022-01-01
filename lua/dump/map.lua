local helper = require("helper")
local map = {}

--- Maps an item prototype.
--- @param prototype table The item prototype to map.
--- @return table|nil The mapped data.
function map.item(prototype)
    if not prototype.valid then
        return nil
    end

    return {
        type = "item",
        name = prototype.name,
        localisedName = prototype.localised_name,
        localisedDescription = prototype.localised_description,
    }
end

--- Maps a fluid prototype.
--- @param prototype table The fluid prototype to map.
--- @return table|nil The mapped data.
function map.fluid(prototype)
    if not prototype.valid then
        return nil
    end

    return {
        type = "fluid",
        name = prototype.name,
        localisedName = prototype.localised_name,
        localisedDescription = prototype.localised_description,
    }
end

--- Maps an entity prototype to a resource.
--- @param prototype table The entity prototype.
--- @return table|nil The mapped data
function map.resource(prototype)
    if not prototype.valid or prototype.type ~= "resource" then
        return nil
    end

    return {
        type = "resource",
        name = prototype.name,
        localisedName = prototype.localised_name,
        localisedDescription = prototype.localised_description,
    }
end


--- Maps an entity prototype to a machine, including mining drills.
--- @param prototype table The machine prototype to map.
--- @return table|nil The mapped data.
function map.machine(prototype)
    if not prototype.valid or (not prototype.crafting_categories and not prototype.resource_categories) then
        return nil
    end

    -- Only allow the character called "character" and ignore all other pseudo-characters
    if prototype.type == "character" and prototype.name ~= "character" then
        return nil
    end

    local energyUsage, energyUsageUnit = helper.convertEnergyUsage((prototype.energy_usage or 0) * 60)
    local machine = {
        name = prototype.name,
        localisedName = prototype.localised_name,
        localisedDescription = prototype.localised_description,
        craftingCategories = helper.extractCategories(prototype.crafting_categories),
        resourceCategories = helper.extractCategories(prototype.resource_categories),
        speed = prototype.crafting_speed or prototype.mining_speed,
        numberOfItemSlots = 0,
        numberOfFluidInputSlots = 0,
        numberOfFluidOutputSlots = 0,
        numberOfModuleSlots = prototype.module_inventory_size,
        energyUsage = energyUsage,
        energyUsageUnit = energyUsageUnit,
    }

    if prototype.type == "furnace" then
        -- Furnaces are forced to have exactly one ingredient slot, but it is not set in ingredient_count.
        machine.numberOfItemSlots = 1
    elseif not prototype.ingredient_count then
        -- Character entities do not specify an ingredient_count, but actually have unlimited ones.
        machine.numberOfItemSlots = 255
    else
        machine.numberOfItemSlots = prototype.ingredient_count
    end

    for _, fluidbox in pairs(prototype.fluidbox_prototypes) do
        if fluidbox.production_type == "input" then
            machine.numberOfFluidInputSlots = machine.numberOfFluidInputSlots + 1
        elseif fluidbox.production_type == "output" then
            machine.numberOfFluidOutputSlots = machine.numberOfFluidOutputSlots + 1
        elseif fluidbox.production_type == "input-output" then
            machine.numberOfFluidInputSlots = machine.numberOfFluidInputSlots + 1
            machine.numberOfFluidOutputSlots = machine.numberOfFluidOutputSlots + 1
        end
    end

    return machine
end


--- Maps a recipe prototype.
--- @param prototype table The recipe prototype to map.
--- @param mode string The mode of the recipe.
--- @return table|nil The mapped data.
function map.recipe(prototype, mode)
    if not prototype.valid then
        return nil
    end

    local recipe = {
        type = "recipe",
        name = prototype.name,
        mode = mode,
        localisedName = prototype.localised_name,
        localisedDescription = prototype.localised_description,
        time = prototype.energy,
        category = prototype.category,
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

--- Maps an entity prototype to its mining recipe.
--- @param prototype table The entity prototype.
--- @return table|nil The mapped data.
function map.miningRecipe(prototype)
    if not prototype.valid or prototype.type ~= "resource" then
        return nil
    end

    local recipe = {
        type = "mining",
        name = prototype.name,
        mode = "normal",
        localisedName = prototype.localised_name,
        localisedDescription = prototype.localised_description,
        category = prototype.resource_category,
        ingredients = {
            {
                type = "resource",
                name = prototype.name,
                amount = 1.
            },
        },
        products = {},
    }

    if prototype.mineable_properties.required_fluid and prototype.mineable_properties.fluid_amount > 0 then
        table.insert(recipe.ingredients, {
            type = "fluid",
            name = prototype.mineable_properties.required_fluid,
            amount = prototype.mineable_properties.fluid_amount,
        })
    end

    for _, product in pairs(prototype.mineable_properties.products) do
        table.insert(recipe.products, map.product(product))
    end

    return recipe
end

--- Maps an item prototype to its rocket launch recipe.
--- @param prototype table The item prototype to map.
--- @return table|nil The mapped data.
function map.rocketLaunchRecipe(prototype)
    if not prototype.valid or next(prototype.rocket_launch_products) == nil then
        return nil
    end

    local recipe = {
        type = "rocket-launch",
        name = prototype.name,
        mode = "normal", -- No expensive mode possible for rocket launch recipes
        localisedName = prototype.localised_name,
        localisedDescription = prototype.localised_description,
        ingredients = {
            {
                type = "item",
                name = prototype.name,
                amount = 1.
            },
        },
        products = {},
     }

     for _, product in pairs(prototype.rocket_launch_products) do
        table.insert(recipe.products, map.product(product))
     end

     return recipe
end

--- Maps an ingredient of a recipe.
--- @param prototype table  The ingredient to map.
--- @return table The mapped data.
function map.ingredient(prototype)
    return {
        type = prototype.type,
        name = prototype.name,
        amount = prototype.amount,
    }
end

--- Maps an product of a recipe.
--- @param prototype table The product to map.
--- @return table The mapped data.
function map.product(prototype)
    local product = {
        type = prototype.type,
        name = prototype.name,
        amountMin = prototype.amount_min or 1.,
        amountMax = prototype.amount_max or 1.,
        probability = prototype.probability or 1.,
    }

    if prototype.amount then
        -- amount is a simpler way of defining the minimum and maximum value.
        product.amountMin = prototype.amount
        product.amountMax = prototype.amount
    end

    return product
end

--- Maps a technology prototype.
--- @param prototype table The technology prototype to map.
--- @param mode string The mode of the technology.
--- @return table|nil The mapped data.
function map.technology(prototype, mode)
    if not prototype.valid then
        return nil
    end

    local technology = {
        name = prototype.name,
        mode = mode,
        localisedName = prototype.localised_name,
        localisedDescription = prototype.localised_description,
        prerequisites = {},
        researchIngredients = {},
        researchCount = prototype.research_unit_count,
        researchTime = prototype.research_unit_energy / 60.,
        unlockedRecipes = {},
        level = prototype.level,
        upgrade = prototype.upgrade,
        maxLevel = prototype.max_level,
        researchCountFormula = prototype.research_unit_count_formula,
    }

    for name in pairs(prototype.prerequisites) do
        table.insert(technology.prerequisites, name)
    end

    for _, ingredient in pairs(prototype.research_unit_ingredients) do
        table.insert(technology.researchIngredients, map.ingredient(ingredient))
    end

    for _, effect in pairs(prototype.effects) do
        if effect.type == "unlock-recipe" then
            table.insert(technology.unlockedRecipes, effect.recipe)
        end
    end

    return technology
end

--- Maps an icon from the prototype.
--- @param prototype table The prototype to map.
--- @return table|nil The mapped data, if there was anything to map.
function map.icon(prototype)
    local icon = {
        type = prototype.type,
        name = prototype.name,
        size = 64,
        layers = {},
    }
    if (prototype.type == "technology") then
        icon.size = 256
    end

    local layered_icons
    if prototype.icons then
        layered_icons = prototype.icons
    elseif type(prototype.icon) == "string" then
        -- Fallback to simple icon definition.
        layered_icons = {
            {
                icon = prototype.icon,
                icon_size = prototype.icon_size,
            },
        }
    else
        return nil
    end

    local first_layer_size = prototype.icon_size or 32
    if layered_icons[1] and layered_icons[1].icon_size then
        first_layer_size = layered_icons[1].icon_size
    end

    for _, layer in pairs(layered_icons) do
        table.insert(icon.layers, map.layer(layer, first_layer_size, prototype.icon_size or 32))
    end
    return icon
end

--- Maps an icon layer from the prototype.
--- @param prototype table The prototype to map.
--- @param first_layer_size number The size of the first layer.
--- @param default_size number The default size of the prototype.
--- @return table The mapped data.
function map.layer(prototype, first_layer_size, default_size)
    local size = prototype.icon_size or default_size
    local scale = prototype.scale or (32 / size)
    local ratio = first_layer_size / 32

    local layer = {
        fileName = prototype.icon,
        size = size,
        scale = scale * ratio,
    }

    if prototype.shift then
        layer.offset = {
            x = prototype.shift[1] * ratio,
            y = prototype.shift[2] * ratio,
        }
    end

    if prototype.tint then
        layer.tint = {
            red = helper.convertColorValue(prototype.tint.r or prototype.tint[1]),
            green = helper.convertColorValue(prototype.tint.g or prototype.tint[2]),
            blue = helper.convertColorValue(prototype.tint.b or prototype.tint[3]),
            alpha = helper.convertColorValue(prototype.tint.a or prototype.tint[4]),
        }
    end

    return layer
end

return map
