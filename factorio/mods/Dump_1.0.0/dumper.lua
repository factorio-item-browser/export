local json = require 'json'

local defaultValue = function(value, defaultValue)
    local result = value
    if (result == nil) then
        result = defaultValue
    end
    return result
end

-- Adds an icon to be used later.
-- @param {table} prototype
-- @return {table|nil}
local prepareIcon = function(prototype)
    local result
    local icons = prototype.icons

    if (icons == nil) and (prototype.icon ~= nil) then
        icons = {
            { icon = prototype.icon }
        }
    end

    if (icons ~= nil) then
        local type = prototype.type
        if type ~= 'recipe' and type ~= 'fluid' and type ~= 'technology' then
            type = 'item'
        end

        result = {
            type = type,
            name = prototype.name,
            icons = icons
        }
    end
    return result
end


-- Prepares the data of the specified fluid prototype to be dumped as item.
-- @param {LuaFluidPrototype} fluid
-- @return {table}
local prepareFluidPrototype = function(fluid)
    local result = {
        name = fluid.name,
        localised = {
            name = fluid.localised_name,
            description = fluid.localised_description
        }
    }
    return result
end

-- Prepares the data of the specified item prototype to be dumped.
-- @param {LuaItemPrototype} item
-- @return {table}
local prepareItemPrototype = function(item)
    local result = {
        name = item.name,
        localised = {
            name = item.localised_name,
            description = item.localised_description
        }
    }
    if item.place_result ~= nil then
        result.localised.entityName = item.place_result.localised_name
        result.localised.entityDescription = item.place_result.localised_description
    end
    return result
end

-- Prepares the data of the specified recipe prototype to be dumped.
-- @param {LuaRecipePrototype}
-- @return {table}
local prepareRecipePrototype = function(recipe)
    local result = {
        name = recipe.name,
        localised = {
            name = recipe.localised_name,
            description = recipe.localised_description
        },
        craftingTime = recipe.energy,
        craftingCategory = recipe.category,
        ingredients = {},
        products = {}
    }
    for _, ingredient in pairs(recipe.ingredients) do
        result.ingredients[ingredient.type .. '|' .. ingredient.name] = {
            type = ingredient.type,
            name = ingredient.name,
            amount = ingredient.amount
        }
    end
    for _, product in pairs(recipe.products) do
        local key = product.type .. '|' .. product.name
        result.products[key] = {
            type = product.type,
            name = product.name
        }
        if product.amount ~= nil then
            result.products[key].amountMin = product.amount
            result.products[key].amountMax = product.amount
            result.products[key].probability = 1
        else
            result.products[key].amountMin = defaultValue(product.amount_min, 1)
            result.products[key].amountMax = defaultValue(product.amount_max, 1)
            result.products[key].probability = defaultValue(product.probability, 1)
        end
    end

    return result
end

-- Dumps the specified data to the log.
-- @param {string} name
-- @param {table} data
local dump = function(name, data)
    log(name .. '>>>---' .. json.encode(data) .. '---<<<' .. name)
end

return {
    dump = dump,
    prepareIcon = prepareIcon,
    prepareFluidPrototype = prepareFluidPrototype,
    prepareItemPrototype = prepareItemPrototype,
    prepareRecipePrototype = prepareRecipePrototype
}