local helper = {}

--- Converts the color value to be always between 0 and 1.
--- @param value number|nil The value to convert.
--- @return number|nil The converted value.
function helper.convertColorValue(value)
    if value == nil then
        return nil
    end

    if value > 1. then
        return value / 255.
    end

    return value
end

--- Converts the energy usage, making the value below 1000 and choosing the correct unit for it.
--- @param value number The value to convert.
--- @return number The converted value.
--- @return string The unit for the converted value.
function helper.convertEnergyUsage(value)
    local energyUsageUnits = {"W", "kW", "MW", "GW", "TW", "PW", "EW", "ZW", "YW"}
    local unit = "W"
    for _, currentUnit in pairs(energyUsageUnits) do
        if value < 1000 then
            unit = currentUnit
            break
        end
        value = value / 1000.
    end

    return math.floor(value * 1000) / 1000., unit
end

--- Extracts the categories from the dictionary of flags.
--- @param categories table|nil The category flags.
--- @return table The extracted categories as list.
function helper.extractCategories(categories)
    if not categories then
        return {}
    end

    local result = {}
    for category, flag in pairs(categories) do
        if flag then
            table.insert(result, category)
        end
    end
    return result
end

return helper