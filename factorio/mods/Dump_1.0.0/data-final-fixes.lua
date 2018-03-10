local dumper = require 'dumper'
local dump = {}

local function searchPrototypes(items)
    if (type(items) == 'table') then
        for _, item in pairs(items) do
            if item.type ~= nil and item.name ~= nil then
                local icons = dumper.prepareIcon(item)
                if (icons ~= nil) then
                    dump[icons.type .. '|' .. icons.name] = icons;
                end
            else
                searchPrototypes(item)
            end
        end
    end
end

searchPrototypes(data.raw)
dumper.dump('ICONS', dump)