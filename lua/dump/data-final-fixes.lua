local dump = require("dump")
local map = require("map")

local function walkPrototypes(prototypes)
    if type(prototypes) == "table" then
        for _, prototype in pairs(prototypes) do
            if prototype.type and prototype.name then
                dump.write("icon", map.icon(prototype))
            else
                walkPrototypes(prototype)
            end
        end
    end
end

walkPrototypes(data.raw)
