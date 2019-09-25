local dump = require("dump")
local map = require("map")

local dump_data = {
    icons = {},
}

local function walk_prototypes(prototypes)
    if type(prototypes) == "table" then
        for _, prototype in pairs(prototypes) do
            if prototype.type and prototype.name then
                table.insert(dump_data.icons, map.icon(prototype))
            else
                walk_prototypes(prototype)
            end
        end
    end
end

walk_prototypes(data.raw)

dump.write("DATA", dump_data)
