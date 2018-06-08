local dumper = require 'dumper'
local dumpIcons = {}
local dumpFluidBoxes = {}

-- Recursively searches for any prototypes to be dumped.
-- @param {table} prototypes
local function searchPrototypes(prototypes)
    if (type(prototypes) == 'table') then
        for _, prototype in pairs(prototypes) do
            if prototype.type ~= nil and prototype.name ~= nil then
                local icons = dumper.prepareIcon(prototype)
                local fluidBoxes = dumper.prepareFluidBoxes(prototype)
                if icons ~= nil then
                    dumpIcons[icons.type .. '|' .. icons.name] = icons;
                end
                if fluidBoxes ~= nil then
                    dumpFluidBoxes[fluidBoxes.type .. '|' .. fluidBoxes.name] = fluidBoxes;
                end
            else
                searchPrototypes(prototype)
            end
        end
    end
end

searchPrototypes(data.raw)
dumper.dump('ICONS', dumpIcons)
dumper.dump('FLUID_BOXES', dumpFluidBoxes)